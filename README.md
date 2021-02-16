A PHP 5.6+ compatible dependency injection container inspired
by [Laravel IOC](https://laravel.com/docs/5.0/container "Service Container - Laravel - The PHP Framework For Web Artisans")
and [Pimple](http://pimple.sensiolabs.org/ "Pimple - A simple PHP Dependency Injection Container") that works even
better on newer version of PHP.

A quick overview of the Container features:

* **Auto-wiring** - The Container will use Reflection to find what class should be built and how, it's almost magic.
* **Flexible** - Legacy code? Fat constructors with tons of side-effects? The Container offers auto-wiring without making it a requirement. It will adapt to existing code and will not require your code to adapt to it. 
* **Fatal error handling** - On PHP 7.0+ the Container will take care of handling fatal errors that might happen at class file load time and handle them.
* **Fast** - The Container is optimized for speed as much as it can be squeezed out of the required PHP compatibility.
* **Flexible default mode** - Singleton (build at most once) an prototype (build new each time) default modes available.
* **Global Application** - Like using `App::get($id)`? The `App` facade allows using the DI Container as a globally available Service Locator.
* **PSR-11 compatible** - The container is fully compatible with [PSR-11 specification](https://www.php-fig.org/psr/psr-11/).
* **Ready for WordPress and other Event-driven frameworks** - The container API provides methods like [`callback`](#the-callback-method) and [`instance`](#the-instance-method) to easily be integrated with Event-driven frameworks like WordPress.
* **Service Providers** - To keep your code organized, the library provides an [advanced Service Provider implementation](#service-providers).

## Table of Contents

* [Code Example](#code-example)
* [Installation](#installation)
* [Upgrading from version 2](#upgrading-from-version-2-to-version-3)
* [Quick and dirty introduction to dependency injection](#quick-and-dirty-introduction-to-dependency-injection)
* [Binding and getting implementations](#binding-and-getting-implementations)
* [Usage in WordPress and other event-driven frameworks](#usage-in-wordpress-and-other-event-driven-frameworks)

## Code Example

In the application bootstrap file we define how the componentns will come together:

```php
<?php
/**
 * The application bootstrap file: here the container is provided the minimal set of instructions
 * required to set up the application objects.
 */

namespace lucatume\DI52\Example1;

use lucatume\DI52\App;
use lucatume\DI52\Container;

require_once __DIR__ . '/vendor/autoload.php';

// Start by building an instance of the DI container.
$container = new Container();

// When an instance of `TemplateInterface` is required, build and return an instance
// of `PlainPHPTemplate`; build at most once (singleton).
$container->singleton(
    TemplateInterface::class,
    static function () {
        return new PlainPHPTemplate(__DIR__ . '/templates');
    }
);

// The default application Repository is the Posts one.
// When a class needs an instance of the `RepositoryInterface`, then
// return an instance of the `PostsRepository` class.
$container->bind(RepositoryInterface::class, PostsRepository::class);

// But the Users page should use the Users repository.
$container->when(UsersPageRequest::class)
    ->needs(RepositoryInterface::class)
    ->give(UsersRepository::class);

// The `UsersRepository` will require a `DbConnection` instance, that
// should be built at most once (singleton).
$container->singleton(DbConnection::class);

// Set the routes.
$container->bind('home', HomePageRequest::class);
$container->bind('users', UsersPageRequest::class);

// Make the container globally available as a service locator using the App.
App::setContainer($container);
```

In the application entrypoint, the `index.php` file, we'll **lazily** resolve the whole dependency tree following the rules set in the
bootstrap file:

```php
<?php
use lucatume\DI52\App;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/bootstrap.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = basename(basename($path, '.php'), '.html') ?: 'home';
?>

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>di52 Example 1</title>
    </head>
    <body>
        <section>
            <header>
                <h2>Path and Route</h2>
            </header>
            <ul>
                <li><strong>Path: </strong><?php echo $path; ?></li>
                <li><strong>Route: </strong><?php echo $route; ?></li>
            </ul>
        </section>
        <main>
            <?php
            App::get($route)->serve(); ?>
        </main>
    </body>
</html>
```

That's it.

You can find the full code for this example in the `docs/examples/01-application`](docs/examples/01-application) directory.

## Installation
Use [Composer](https://getcomposer.org/) to require the library:

```bash
composer require lucatume/di52
```

Include the [Composer](https://getcomposer.org/) autoload file in your project entry point and create a new instance of
the container to start using it:

```php
<?php
require_once 'vendor/autoload.php';

$container = new lucatume\DI52\Container();

$container->singleton(DbInterface::class, MySqlDb::class);
```

If you would prefer using the Dependency Injection Container as a globally-available Service Locator, then you 
can use the `lucatume\DI52\App`:

```php
<?php
require_once 'vendor/autoload.php';

lucatume\DI52\App::singleton(DbInterface::class, MySqlDb::class);
```

See the [example above](#code-example) for more usage examples.

## Upgrading from version 2 to version 3

The main change introduced by version `3.0.0` of the library is dropping compatibility with PHP 5.2 to require a minimum
version of PHP 5.6. The library is tested up to PHP 8.0.

If you're using version 2 of DI52 in your project, then there should be nothing you need to do.  
The new, namespaced, classes of version 3 are aliased to their version 2 correspondent, e.g. `tad_DI52_Container` is
aliased to `lucatume\di52\Container` and `tad_DI52_ServiceProvider` is aliased to `lucatume\di52\ServiceProvider`.

I suggest an update for **a performance gain**, though, to use the new, namespaced, class names in place of the PHP 5.2
compatible ones:

* replace uses of `tad_DI52_Container` with `lucatume\di52\Container`
* replace uses of `tad_DI52_ServiceProvider` with `lucatume\DI52\ServiceProvider`

The new version implemented [PSR-11](https://www.php-fig.org/psr/psr-11/) compatibility and the main method to get hold
of an object instance from the container changed from `make` to `get`.  
Do not worry, the `lucatume\di52\Container::make` method is still there: it's just an alias of
the `lucatume\di52\Container::get` one.  
For another small performance gain replace uses of `tad_DI52_Container::make` with `lucatume\di52\Container::get`.

That should be all of it.

## Quick and dirty introduction to dependency injection

### What is dependency injection?

A [Dependency Injection (DI) Container](https://en.wikipedia.org/wiki/Dependency_injection "Dependency injection - Wikipedia")
is a tool meant to make dependency injection possible and easy to manage.  
Dependencies are specified by a class constructor method via [**type-hinting**](http://php.net/manual/en/language.oop5.typehinting.php "PHP: Type Hinting - Manual"):

```php
class A {
    private $b;
    private $c; 

    public function __construct(B $b, C $c){
        $this->b = $b;
        $this->c = $c;
    }
}
```

Any instance of class `A` **depends** on implementations of the `B` and `C` classes.  
The "injection" happens when class `A` dependencies are passed to it, "injected" in its constructor method, in place of
being created inside the class itself.

```php
$a = new A(new B(), new C());
```

The flexibility of type hinting allows injecting into `A` not just instances of `B` and `C` but instances of any class
extending the two:

```php
class ExtendedB extends B {}

class ExtendedC extends C {}

$a = new a(new ExtendedB(), new ExtendedC());
```

PHP allows type hinting not just **concrete implementations** (classes) but **interfaces** too:

```php
class A {
    private $b;
    private $c; 

    public function __construct(BInterface $b, CInterface $c){
        $this->b = $b;
        $this->c = $c;
    }
}
```

This extends the possibilities of dependency injection even further and avoids strict coupling of the code:

```php
class B implements BInterface {}

class C implements CInterface {}

$a = new a(new B(), new C());
```

### What is a DI container?

The `B` and `C` classes are concrete (as in "you can instance them") implementations of interfaces and while the
interfaces might never change the implementations might and should change in the lifecycle of code: that's
the [Dependency Inversion principle](https://en.wikipedia.org/wiki/Dependency_inversion_principle) or "depend upon
abstraction, non concretions".  
If the implementation of `BInterface` changes from `B` to `BetterB` then I'd have to update all the code where I'm
building instances of `A` to use `BetterB` in place of `B`:

```php

// before
$a = new A(new B(), new C());

//after
$a = new A(new BetterB(), new C());
```

On smaller code-bases this might prove to be a quick solution but, as the code grows, it will become less and less an
applicable solution.  
Adding classes to the mix proves the point when dependencies start to stack:

```php
class D implements DInterface{
    public function __construct(AInterface $a, CInterface $c){}
}

class E {
    public function __construct(DInterface $d){}
}

$a = new A (new BetterB(), new C());
$d = new D($a, $c);
$e = new E($d);
```

Another issue with this approach is that classes have to be built immediately to be injected, see `$a` and `$d` above to
feed `$e`, with the immediate cost of "eager" instantiation, if `$e` is never used than the effort put into building it,
in terms of time and resources spent by PHP to build `$a`, `$b`, `$c`, `$d` and finally `$e`, are wasted.  
A **dependency injection container** is an object that, provided construction templates of objects, will take care of
building only objects that are really needed taking care of **resolving** nested dependencies.

> Need an instance of `E`? I will build instances of `B` and `C` to build an instance of `A` to build an instance of `D` to finally build and return an instance of `E`.

### What is a Service Locator?

A "Service Locator" is an object, or function, that will answer to this question made by your code:

```php
$db = $serviceLocator->get('database');
```

In Plain English "I do not care how it's built or where it comes from, give me the current implementation of the database service.".

Service Locators are, usually, globally-available DI Containers for obvious reasons: the DI Container knows how to build the services the Service Locator will provide when required.  
The concept of Service Locators and DI Containers are often conflated as a DI Container, when globally available, becomes a good implementation of a Service Locator.

An example of this is the `lucatume\DI52\App` class: it will expose, by means of static methods, a globally-available instance of the `lucatume\DI52\Container` class.

```php
<?php
use lucatume\DI52\Container;
use lucatume\DI52\App;

// This is a DI Container.
$diContainer = new Container();

// Register a binding in the DI Container.
$diContainer->singleton('database', MySqlDb::class);

// If we make it globally-available, then it will be used by the Service Locator (the `App` class).
App::setContainer($diContainer);

// We can now globally, i.e. anywhere in the code, access the `db` service.
$db = App::get('database');
```

Since the `lucatume\DI52\App` class proxies calls to the Container, the example could be made shorter:

```php
<?php
use lucatume\DI52\App;

// Register a binding in the App (Service Locator).
App::singleton('database', MySqlDb::class);

// We can now globally, i.e. anywhere in the code, access the `db` service.
$db = App::get('database');
```

### Construction templates

The container will need to be told, just once, how objects should be built.  
For the container it's easy to understand that a class type-hinting an instance of the concrete class `A` will require a
new instance of `A` but loosely coupled code leveraging the use of a DI container will probably type-hint an `interface`
in place of concrete `class`es.  
Telling the container what concrete `class` to instance when a certain `interface` is requested by an
object `__construct` method is called "binding and implementation to an interface".  
While dependency injection can be made in other methods too beyond the `__construct` one that's what di52 supports at
the moment; if you want to read more the web is full of good reference
material, [this article by Fabien Potencier](http://fabien.potencier.org/what-is-dependency-injection.html) is a very
good start.

## The power of make

At its base the container is a dependency resolution and injection machine: given a class to its `make` method it will
read the class type-hinted dependencies, build them and inject them in the class.

```php
// file ClassThree.php
class ClassThree {
    private $one;
    private $two;

    public function __construct(ClassOne $one, ClassTwo $two){
        $this->one = $one;
        $this->two = $two;
    }
}

// application bootstrap file
$container = new tad_DI52_Container();

$three = $container->make('ClassThree');
```

Keep that in mind while reading the following paragraphs.

## Storing variables

In its most basic use case the container can store variables:

```php
$container = new tad_DI52_Container();

$container->setVar('foo', 23);

$foo = $container->getVar('foo');
```

Since di52 will treat any callable object as a factory (see below) callables have to be protected using the
container `protect` method:

```php
$container = new tad_DI52_Container();

$container->setVar('foo.factory', $container->protect(function($val){
    return $val + 23;
}));

$foo = $container->getVar('foo');
```

## Binding implementations

Once an instance of di52 is available telling it what should be built and when is quite easy; di52 proposes the same API
exposed
by [Laravel Service container](https://laravel.com/docs/5.3/container "Service Container - Laravel - The PHP Framework For Web ...")
and while the inner workings are different the good idea (Laravel's) is reused.  
Reusing the example above:

```php
$container = new tad_DI52_Container();

// binding
$container->bind('AInterface', 'A');
$container->bind('BInterface', 'BetterB');
$container->bind('CInterface', 'C');
$container->bind('DInterface', 'D');

// constructing
$e = $container->make('E');
```

The `make` method will build the `E` object resolving its requirements to the bound implementations when requested.  
When using the `bind` method a new instance of the bound implementations will be returned on each request; this might
not be the wanted behaviour especially for object costly to build (like a database driver that needs to connect): in
that case the `singleton` method should be used:

```php
$container = new tad_DI52_Container();

$container->singleton('DBDriverInterface', 'MYSqlDriver');
$container->singleton('RepositoryInterface', 'MYSQLRepository');

$container->make('RepositoryInterface');
```

Binding an implementation to an interface using the `singleton` methods tells the container the implementations should
be built just the first time: any later call for that same interface should return the same instance.  
Implementations can be redefined in any moment simple calling the `bind` or `singleton` methods again specifying a
different implementation.

## Binding objects

The container allows binding (using the `bind` or the `singleton` method) more than just implementations in the form of
class names to take into account various scenarios where objects might be pre-existing in previous code or built in an
other way; when binding an object that same object will be returned each time making the use of `bind` and `singleton`
equivalent:

```php
$container = new tad_DI52_Container();

// a globally stored instance of the database handling class
global $db;

$container->bind('DBInterface', $db);

// binding an object that's built using a factory 
$container->bind('RepositoryInterface', RepositoryFactory::make('post'));

if($condition) {
    $handler = new HandlerOne();
} else {
    $handler = new HandlerTwo();
}

// binding an object that's built in some other way
$container->bind('HandlerInterface', $handler);
```

## Binding closures on PHP 5.3+

All of the cases above suffers from an "eager instantiation" that's far from being ideal; if that's the case and the
code runs on PHP 5.3+ then closures can be bound as factories using `bind` and as singletons using `singleton`:

```php
$container = new tad_DI52_Container();

// binding an object that's built using a factory using a closure
$container->bind('RepositoryInterface', function(){
   return  RepositoryFactory::make('post');
});

// binding an object that's built in some other way
$container->singleton('HandlerInterface', function(){
    if($condition) {
        $handler = new HandlerOne();
    } else {
        $handler = new HandlerTwo();
    }
});
```

Closures will receive the container itself as an argument and will be able to leverage its resolution power:

```php
$container = new tad_DI52_Container();

$container->make('DBDriverInterface', 'MYSQLDriver');

$container->bind('DBInterface', function(tad_DI52_Container $container){
    $dbDriver = $container->make('DBDriverInterface');

    return new DBConnection($dbDriver);
});
```

## Binding implementations to classes

Binding implementations to interfaces works when the object constructor methods type hint interfaces but that might not
always be the case; the container will handle that case allowing implementations to be bound to classes supporting both
the `bind` and the `singleton` methods:

```php
// file LegacyClass.php
class LegacyClass {
    public function __construct(ClassOne $one, ClassTwo $two){}
}

// app bootstrap file
$container = new tad_DI52_Container();

$container->bind('ClassOne', 'ModernClassOne');
$container->bind('ClassTwo', 'ModernClassTwo');

// the container will inject instances of the `ModernClassOne` and `ModernClassTwo` classes
$container->make('LegacyClass');
```

### Registering concrete instantiable classes as singleton

The container knows how to `make` instances of a concrete, instantiatable class, and there might be situations where the
requirement is to have `make` return the same singleton instance of a class on each call.

In those situations the binding syntax can be shortened to one parameter in place of two:

```php
// I want the `ClassOne` instance returned by the container to be the same every time.

// app bootstrap file
$container = new tad_DI52_Container();

// This is the short syntax to instruct the container to bind `ClassOne` as a singleton.
// This is the short form of this: $container->singleton(ClassOne::class, ClassOne::class);
$container->singleton(ClassOne::class);

assert($container->make(ClassOne::class) === $container->make(ClassOne::class));
```

## Binding implementations to slugs

The container was heavily inspired
by [Pimple](http://pimple.sensiolabs.org/ "Pimple - A simple PHP Dependency Injection Container") and offers some
features of the PHP 5.3+ DI container as well:

```php
$container = new tad_DI52_Container();

$container['db.driver'] = 'MYSQLDriver';

// storing a closure as a singleton constructor
$container['db.connection'] = function($container){
    $dbDriver = $container->make('db.driver');

    return new DBConnection($dbDriver);
};

$dbConnection = $container['db.connection'];

// storing a closure as a var
$container['uniqid'] = $container->protect(function(){
    return uniqid('id', true);
});

// storing vars
$container['db.name'] = 'appDb';
$container['db.user'] = 'root';
$container['db.pass'] = '';
$container['db.host'] = 'localhost';

// getting vars
$dbName = $container['db.name'];
$dbUser = $container['db.user'];
$dbPass = $container['db.pass'];
$dbHost = $container['db.host'];
```

There is no replacement for the `factory` method offered by Pimple: the `bind` method should be used instead.

## Contextual binding

Borrowing an excellent idea from Laravel's container the possibility of contextual binding exists (supporting all the
binding possibilities above).  
Contextual binding solves the problem of different objects requiring different implementations of the same interface (or
class, see above):

```php
$container = new tad_DI52_Container();

// by default any object requiring an implementation of the `CacheInterface`
// should be given the same instance of `Array Cache`
$container->singleton('CacheInterface', 'ArrayCache');

$container->bind('DbCache', function($container){
    $cache = $container->make('CacheInterface');
    $dbCache = new DbCache($cache);
    $dbCache->prime();
    $dbCache->dump();

    return $dbCache;
});

// but when an implementation of the `CacheInterface` is requested by `TransactionManager`
// give it an instance of `DbCache` instead
$container->when('TransactionManager')
    ->needs('CacheInterface')
    ->give('DbCache');
```

## After-build methods

When working on PHP 5.2 compatible code closures will not be available and while that does impose limits di52 tries to "
limit the damage".  
The last code example could be rewritten in PHP 5.2 compatible code leveraging the container support for after-build
methods: methods that will be called **with no arguments** on the built objects after it has been built:

```php
$container = new tad_DI52_Container();

$container->singleton('CacheInterface', 'ArrayCache');

$container->singleton('DbCache', 'DbCache', array('prime', 'dump'));

$container->when('TransactionManager')
    ->needs('CacheInterface')
    ->give('DbCache');
```

After-build methods can be specified as the third argument of the `bind` an `singleton` methods.  
If the implementation is bound using `singleton` then the after-build methods will be called only the first time when
the object is built.

## Binding decorator chains

The [Decorator pattern](https://en.wikipedia.org/wiki/Decorator_pattern "Decorator pattern - Wikipedia") allows
extending the functionalities of an implementation without creating an extension and leveraging interfaces.  
In very simple terms:

```php

interface EndpointInterface {
    public function get();
}

class BaseEndpoint implements EndpointInterface {
    private $repository;

    public function __construct(ReposistoryInterface $repository) {
        $this->repository = $repository;
    }

    public function get() {
        return $this->repository->getAll();
    }
}

class CachingEndpoint implements EndpointInterface {
    private $decorated;
    private $cache;

    public function __construct(EndpointInterface $decorated, CacheInterface $cache ) {
        $this->decorated = $decorated;
        $this->cache = $cache;
    }

    public function get() {
        $data = $this->cache->get('data')

        if(false === $data) {
            $data = $this->decorated->get();
            $this->cache->set('data', $data);
        }

        return $data;
    }
}

class LoggingEndpoint implements EndpointInterface {
    private $decorated;
    private $logger;

    public function __construct(EndpointInterface $decorated, LoggerInterface $logger ) {
        $this->decorated = $decorated;
        $this->logger = logger$;
    }

    public function get() {
        $this->logger->logRequest('get');
        $data = $this->decorated->get();

        return $data;
    }
}
```

The container allows binding "chain of decorators" to an interface (or slug a la Pimple, or class) using
the `bindDecorators` and `singletonDecorators`.  
The two methods are the `bind` and `singleton` equivalents for decorators.  
The two methods can be skipped on PHP 5.3+ code:

```php
$container = new tad_DI52_Container();

$container->bind('RepositoryInterface', 'PostRepository');
$container->bind('CacheInterface', 'ArrayCache');
$container->bind('LoggerInterface', 'FileLogger');

$container->bind('PostEndpoint', function($container){
    $base = $container->make('BaseEndpoint');
    $caching = new CachingEndpoint($base, $container->make('CacheInterface'));
    $logging = new LoggingEndpoint($caching, $container->make('LoggerInterface'));
    
    return $logging;
});
```

But becomes necessary on PHP 5.2 compatible code:

```php
$container = new tad_DI52_Container();

$container->bind('CacheInterface', 'ArrayCache');
$container->bind('LoggerInterface', 'FileLogger');
    
// decorate right to left, last is built first!
$container->bindDecorators('EndpointInterface', array('LoggingEndpoint', 'CachingEndpoint', 'BaseEndpoint'));

$postEndpoint = $container->make('EndpointInterface');
```

## Tagging

Tagging allows grouping similar implementations for the purpose of referencing them by group.  
Grouping implementations makes sense when, as an example, the same method has to be called on each implementation:

```php
$container = new tad_DI52_Container();

$container->bind('UnsupportedEndpoint', function($container){
    $template = '404';
    $message = 'Nope';
    $redirectAfter = 3;
    $redirectTo = $container->make('HomeEndpoint');

    return new UnsupportedEndpoint($template, $message, $redirectAfter, $redirectTo);
});

$container->tag(array('HomeEndpoint', 'PostEndpoint', 'UnsupportedEndpoint'), 'endpoints');

foreach($container->tagged('enpoints') as $endpoint) {
    $endpoint->register();
}
```

The `tag` method supports any possibility offered by the container in terms of binding of objects, closures, decorator
chains and after-build methods.

## The instance method

In the example above the `UnsupportedEndpoint` requires three primitive parameters and an endpoint to be built and the
method used above relies on closures only available in PHP 5.3+.  
To offer a degree of support the container offers the `instance` method that allows rewriting the code above to this:

```php
$container = new tad_DI52_Container();

$container->bind('UnsupportedEndpoint', $container->instance('404', 'Nope', 3, 'HomeEndpoint'));

$container->tag(array('HomeEndpoint', 'PostEndpoint', 'UnsupportedEndpoint'), 'enpoints');

foreach($container->tagged('enpoints') as $endpoint) {
    $endpoint->register();
}
```

The instance methods does not offer the same amount of flexibility closures offer (and that's why closures were
implemented) but mitigates the problem avoiding other work-arounds (singletons, factories or an eager instantiation) and
granting a **lazy instantiation**.

## The callback method

Some applications require callbacks (or some form of callable) to be returned in specific pieces of code.  
This is especially the case with WordPress and
its [event-based architecture](https://codex.wordpress.org/Plugin_API/Filter_Reference "Plugin API/Filter Reference « WordPress Codex")
.  
Using the container does not removes that possibility:

```php
$container = new tad_DI52_Container();

$container->bind('FilterInterface', 'ConcreteFilter');

add_filter('some_filter', array($container->make('FilterInterface'), 'filter'));
```

This code suffers, but, from an eager instantiation problem: `ConcreteFilter` is built for the purpose of binding it but
might never be used.  
The problem is easy to solve on PHP 5.3+:

```php
$container = new tad_DI52_Container();

$container->bind('FilterInterface', 'ConcreteFilter');

$filterFunction = function($dataToFilter) use($container){
    $filter = $container->make('FilterInterface');

    return $filter->filter($data);
};

add_filter('some_filter', $filterFunction);
```

But this is not an option on PHP 5.2 compatible code.  
In that case the container offers the `callback` method to return a callable function that will **lazily build** the
object, call the method on it passing the call arguments and return its return value:

```php
$container = new tad_DI52_Container();

$container->bind('FilterInterface', 'ConcreteFilter');

add_filter('some_filter', $container->callback('FilterInterface', 'filter'));
```

## Service providers

To avoid passing the container instance around (
see [Service Locator pattern](https://en.wikipedia.org/wiki/Service_locator_pattern "Service locator pattern - Wikipedia"))
or globalising it all the binding should happen in the same PHP file: this could lead, as the application grows, to a
thousand lines monster.  
To avoid that the container supports service providers: those are classes implmenting
the `tad_DI52_ServiceProviderInterface` interface, or extend the ready to use `tad_DI52_ServiceProvider` class, that
allow organizing the binding registrations into logical, self-contained and manageable units:

```php
// file ProviderOne.php
class ProviderOne extends tad_DI52_ServiceProvider {
    public function register() {
        $this->container->bind('InterfaceOne', 'ClassOne');
        $this->container->bind('InterfaceTwo', 'ClassTwo');
        $this->container->singleton('InterfaceThree', 'ClassThree');
    }
}

// application bootstrap file
$container = new tad_DI52_Container();

$container->register('ProviderOne');
```

### Booting service providers

The container implements a `boot` method that will, in turn, call the `boot` method on any service provider that
overloads it.  
Some applications might define constants and environment variables at "boot" time (e.g. WordPress `plugins_loaded`
action) that might make an immediate registration futile.  
In that case service providers can overload the `boot` method:

```php
// file ProviderOne.php
class ProviderOne extends tad_DI52_ServiceProvider {
    public function register() {
        $this->container->bind('InterfaceOne', 'ClassOne');
        $this->container->bind('InterfaceTwo', 'ClassTwo');
        $this->container->singleton('InterfaceThree', 'ClassThree');
    }

    public function boot() {
        if(defined('SOME_CONSTANT')) {
            $this->container->bind('InterfaceFour', 'ClassFour');
        } else {
            $this->container->bind('InterfaceFour', 'AnotherClassFour');
        }
    }
}

// application bootstrap file
$container = new tad_DI52_Container();

$container->register('ProviderOne');
```

### Deferred service providers

Sometimes even just setting up the implementations might require such an up-front cost to make it undesireable unless
it's needed.  
This might happen with non autoloading code that will require a tangle of files to load (and side load) to grab a simple
class instance.  
To "defer" that cost service providers can overload the `deferred` property and the `provides` method:

```php
// file ProviderOne.php
class ProviderOne extends tad_DI52_ServiceProvider {
    public $deferred = true;

    public function provides() {
        return array('LegacyClassOne', 'LegacyInterfaceTwo');
    }

    public function register() {
        include_once('legacy-file-one.php')
        include_once('legacy-file-two.php')

        $db = new Db();

        $details = $db->getDetails();

        $this->container->singleton('LegacyClassOne', new LegacyClassOne($details));
        $this->container->bind('LegacyInterfaceTwo', new LegacyClassTwo($details));
    }
}

// application bootstrap file
$container = new tad_DI52_Container();
    
// the provider `register` method will not be called immediately...
$container->register('ProviderOne');

// ...it will be called here as it provides the binding of `LegacyClassOne`
$legacyOne = $container->make('LegacyClassOne');

// will not be called again here, done already
$legacyTwo = $container->make('LegacyInterfaceTwo');
```
