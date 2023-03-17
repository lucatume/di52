A PHP 5.6+ compatible dependency injection container inspired
by [Laravel IOC](https://laravel.com/docs/5.0/container "Service Container - Laravel - The PHP Framework For Web Artisans")
and [Pimple](http://pimple.sensiolabs.org/ "Pimple - A simple PHP Dependency Injection Container") that works even
better on newer version of PHP.

A quick overview of the Container features:

* **Auto-wiring** - The Container will use Reflection to find what class should be built and how, it's _almost_ magic.
* **Flexible** - Legacy code? Fat constructors with tons of side-effects? The Container offers auto-wiring without
  making it a requirement. It will adapt to existing code and will not require your code to adapt to it.
* **Fatal error handling** - On PHP 7.0+ the Container will take care of handling fatal errors that might happen at
  class file load time and handle them.
* **Fast** - The Container is optimized for speed as much as it can be squeezed out of the required PHP compatibility.
* **Flexible default mode** - Singleton (build at most once) an prototype (build new each time) default modes available.
* **Global Application** - Like using `App::get($service)->doStuff()`? The `App` facade allows using the DI Container as
  a globally available Service Locator.
* **PSR-11 compatible** - The container is fully compatible
  with [PSR-11 specification](https://www.php-fig.org/psr/psr-11/).
* **Ready for WordPress and other Event-driven frameworks** - The container API provides methods
  like [`callback`](#the-callback-method) and [`instance`](#the-instance-method) to easily be integrated with
  Event-driven frameworks like WordPress that require hooking callbacks to events.
* **Service Providers** - To keep your code organized, the library provides
  an [advanced Service Provider implementation](#service-providers).

## Table of Contents

- [Code Example](#code-example)
- [Installation](#installation)
- [Upgrading from version 2 to version 3](#upgrading-from-version-2-to-version-3)
- [Upgrading from version 3.2 to version 3.3](#upgrading-from-version-32-to-version-33)
- [Quick and dirty introduction to dependency injection](#quick-and-dirty-introduction-to-dependency-injection)
  * [What is dependency injection?](#what-is-dependency-injection-)
  * [What is a DI container?](#what-is-a-di-container-)
  * [What is a Service Locator?](#what-is-a-service-locator-)
  * [Construction templates](#construction-templates)
- [The power of `get`](#the-power-of--get-)
- [Storing variables](#storing-variables)
- [Binding implementations](#binding-implementations)
- [Binding implementations to slugs](#binding-implementations-to-slugs)
- [Contextual binding](#contextual-binding)
- [Binding decorator chains](#binding-decorator-chains)
- [Tagging](#tagging)
- [The callback method](#the-callback-method)
- [Service providers](#service-providers)
  * [Booting service providers](#booting-service-providers)
  * [Deferred service providers](#deferred-service-providers)
  * [Dependency injection with service providers](#dependency-injection-with-service-providers)
- [Customizing the container](#customizing-the-container)
  * [Unbound classes resolution](#unbound-classes-resolution)
  * [Exception masking](#exception-masking)

## Code Example

In the application bootstrap file we define how the components will come together:

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

// Bind primitive values, e.g. public function __construct( int $per_page ) {}
$container->when(UsersPageRequest::class)
    ->needs('$per_page')
    ->give(10);

// Fetch the above class without any further definitions
$container->get(UsersPageRequest::class)

// The `UsersRepository` will require a `DbConnection` instance, that
// should be built at most once (singleton).
$container->singleton(DbConnection::class);

// Set the routes.
$container->bind('home', HomePageRequest::class);
$container->bind('users', UsersPageRequest::class);

// Make the container globally available as a service locator using the App.
App::setContainer($container);
```

In the application entrypoint, the `index.php` file, we'll **lazily** resolve the whole dependency tree following the
rules set in the bootstrap file:

```php
<?php
use lucatume\DI52\App;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/bootstrap.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = basename(basename($path, '.php'), '.html') ?: 'home';

App::get($route)->serve(); ?>
?>
```

That's it.

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
version of PHP 5.6. The library is tested up to PHP 8.1.

If you're using version 2 of DI52 in your project, then there _should_ be nothing you need to do.
The new, namespaced, classes of version 3 are aliased to their version 2 correspondent, e.g. `tad_DI52_Container` is
aliased to `lucatume\di52\Container` and `tad_DI52_ServiceProvider` is aliased to `lucatume\di52\ServiceProvider`.

I suggest an update for **a small performance gain**, though, to use the new, namespaced, class names in place of the
PHP 5.2
compatible ones:

* replace uses of `tad_DI52_Container` with `lucatume\di52\Container`
* replace uses of `tad_DI52_ServiceProvider` with `lucatume\DI52\ServiceProvider`

The new version implemented [PSR-11](https://www.php-fig.org/psr/psr-11/) compatibility and the main method to get hold
of an object instance from the container changed from `make` to `get`.
Do not worry, the `lucatume\di52\Container::make` method is still there: it's just an alias of
the `lucatume\di52\Container::get` one.
For another small performance gain replace uses of `tad_DI52_Container::make` with `lucatume\di52\Container::get`.

That should be all of it.

## Upgrading from version 3.2 to version 3.3

Version 3.3.0 of the library removed the `aliases.php` file, which previously helped to load non-PSR namespaced class names.
However, if you're using the `tad_DI52_Container` and `tad_DI52_ServiceProvider` classes in your project, you can set up the aliases by adding a few lines of code to your project's bootstrap file to ensure your code continues to work as expected:

```php
<?php

$aliases = [
    ['lucatume\DI52\Container', 'tad_DI52_Container'],
    ['lucatume\DI52\ServiceProvider', 'tad_DI52_ServiceProvider']
];
foreach ($aliases as list($class, $alias)) {
    if (!class_exists($alias)) {
        class_alias($class, $alias);
    }
}
```

## Quick and dirty introduction to dependency injection

### What is dependency injection?

A [Dependency Injection (DI) Container](https://en.wikipedia.org/wiki/Dependency_injection "Dependency injection - Wikipedia")
is a tool meant to make dependency injection possible and easy to manage.
Dependencies are specified by a class constructor method via
[**type-hinting**](http://php.net/manual/en/language.oop5.typehinting.php "PHP: Type Hinting - Manual"):

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
abstractions, non concretions".
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
A **dependency injection container**  will take care of building only objects that are needed taking care of
**resolving** nested dependencies.

> Need an instance of `E`? I will build instances of `B` and `C` to build an instance of `A` to build an instance of `D`
 to finally build and return an instance of `E`.

### What is a Service Locator?

A "Service Locator" is an object, or function, that will answer to this question made by your code:

```php
$database = $serviceLocator->get('database');
```

In Plain English "I do not care how it's built or where it comes from, give me the current implementation of the
database service.".

Service Locators are, usually, globally-available DI Containers for obvious reasons: the DI Container knows how to build
the services the Service Locator will provide when required.
The concept of Service Locators and DI Containers are often conflated as a DI Container, when globally available,
makes a good implementation of a Service Locator.

An example of this is the `lucatume\DI52\App` class: it will expose, by means of static methods, a globally-available
instance of the `lucatume\DI52\Container` class.

```php
<?php
use lucatume\DI52\Container;
use lucatume\DI52\App;

// This is a DI Container.
$diContainer = new Container();

// If we make it globally-available, then it will be used by the Service Locator (the `App` class).
App::setContainer($diContainer);

// Register a binding in the DI Container.
$diContainer->singleton('database', MySqlDb::class);

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

## The power of `get`

At its base the container is a dependency resolution and injection machine: given a class to its `get` method it will
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

// The application bootstrap file
use lucatume\DI52\Container;

$container = new Container();

$three = $container->get('ClassThree');
```

Keep that in mind while reading the following paragraphs.

## Storing variables

In its most basic use case the container can store variables:

```php
use lucatume\DI52\Container;

$container = new Container();

$container->setVar('number', 23);

$number = $container->getVar('number');
```

Since the container will treat any callable object as a factory (see below) callables have to be protected using the
container `protect` method:

```php
$container = new tad_DI52_Container();

$container->setVar('randomNumberGenerator', $container->protect(function($val){
    return mt_rand(1,100) + 23;
}));

$randomNumberGenerator = $container->getVar('randomNumberGenerator');
```

The protect method tells the container that, when `get`ting the `randomNumberGenerator` alias, we do not want to run the function and
get its result, but we want to get back the function itself.

## Binding implementations

Telling the container what should be built and when is done by an API similar to the one exposed by the [Laravel Service container](https://laravel.com/docs/5.3/container "Service Container - Laravel - The PHP Framework For Web ...")
and while the inner workings are different the good idea (kudos to Laravel's creator and maintainers) is reused.
Reusing the example above:

```php
use lucatume\DI52\Container;

$container = new Container();

// Bind to a class name.
$container->bind(AInterface::class, A::class);
// Bind to a Closure.
$container->bind(BInterface::class, function(){
    return new BetterB();
});
// Bind to a constructor and methods that should be called on the built object.
$container->bind(CInterface::class, LegacyC::class, ['init','register']);
// Bind to a factory method.
$container->bind(D::interface, [DFactory::class,'buildInstance'])
// Bind to an object, it will be a singleton by default.
$container->bind(E::interface, new EImplementation());

$e = $container->get(F::class);
```

The `get` method will build the `F` object resolving its requirements to the bound implementations when requested.
When using the `bind` method a new instance of the bound implementations will be returned on each request; this might
not be the wanted behaviour especially for object costly to build (like a database driver that needs to connect): in
that case the `singleton` method should be used:

```php
use lucatume\DI52\Container;

$container = new Container();

$container->singleton(DBDriverInterface::class, MYSqlDriver::class);
$container->singleton(RepositoryInterface::class, MYSQLRepository::class);

$container->get(RepositoryInterface::class);
```

Binding an implementation to an interface using the `singleton` methods tells the container the implementations should
be built just the first time: any later call for that same interface should return the same instance.
Implementations can be redefined in any moment simple calling the `bind` or `singleton` methods again specifying a
different implementation.

You can customize how unbound classes are resolved by the container, check the [unbound classes](#unbound-classes-resolution) section.

## Binding implementations to slugs

The container was heavily inspired
by [Pimple](http://pimple.sensiolabs.org/ "Pimple - A simple PHP Dependency Injection Container") and offers some
features of the PHP 5.3+ DI container as well:

```php
use lucatume\DI52\Container;

$container = new Container();

// Storing vars using the ArrayAccess API.
$container['db.name'] = 'appDb';
$container['db.user'] = 'root';
$container['db.pass'] = 'secret';
$container['db.host'] = 'localhost:3306';

// Bindings can be set using ArrayAccess methods.
$container['db.driver'] = MYSQLDriver::class;

// Bound closures will receive the container instance as argument.
$container['db.connection'] = function($container){
    $host = $container['db.host']
    $user = $container['db.user'],
    $pass = $container['db.pass'],
    $name = $container['db.name'],

    $dbDriver = $container['db.driver'];
    $dbDriver->connect($host, $user, $pass, $name);

    return new DBConnection($dbDriver);
};

// Equivalent to $container->get('db.connection');
$dbConnection = $container['db.connection'];

// Using ArrayAccess API to store a closure as a variable.
$container['uniqid'] = $container->protect(function(){
    return uniqid('id', true);
});
```

There is no replacement for the `factory` method offered by Pimple: the `bind` method should be used instead.

## Contextual binding

Borrowing an excellent idea from Laravel's container the possibility of contextual binding exists (supporting all the
binding possibilities above).
Contextual binding solves the problem of different objects requiring different implementations of the same interface (or
class, see above):

```php
use lucatume\DI52\Container;

$container = new Container();

/*
 * By default any object requiring an implementation of the `CacheInterface`
 * should be given the same instance of `Array Cache`
 */
$container->singleton(CacheInterface::class, ArrayCache::class);

$container->bind(DbCache::class, function($container){
    $cache = $container->get(CacheInterface::class);
    $dbCache = new DbCache($cache);

    return $dbCache;
});

/*
 * But when an implementation of the `CacheInterface` is requested by
 * `TransactionManager`, then it should be given an instance of `Array Cache`.
 */
$container->when(TransactionManager::class)
    ->needs(CacheInterface::class)
    ->give(DbCache::class);

/*
 * We can also bind primitives where the container doesn't know how to auto-wire
 * them.
 */
$container->when(MysqlOrm:class)
    ->needs('$dbUrl')
    ->give('mysql://user:password@127.0.0.1:3306/app');

/*
 * When primitives are bound to a class the container will correctly resolve them when building the class
 * bound to an interface.
 */
$container->bind(ORMInterface::class, MysqlOrm::class);

// The `ORMInterface` will be resolved an instance of the `MysqlOrm` class, with the `$dbUrl` argument set correctly.
$orm = $container->get(ORMInterface::class);
```

## Binding decorator chains

The [Decorator pattern](https://en.wikipedia.org/wiki/Decorator_pattern "Decorator pattern - Wikipedia") allows
extending the functionalities of an implementation without creating an extension and leveraging interfaces.
The container allows binding "chain of decorators" to an interface (or slug à la Pimple, or class) using
the `bindDecorators` and `singletonDecorators`.
The two methods are the `bind` and `singleton` equivalents for decorators.

```php
use lucatume\DI52\Container;

$container = new Container();

$container->bind(RepositoryInterface::class, PostRepository::class);
$container->bind(CacheInterface::class, ArrayCache::class);
$container->bind(LoggerInterface::class, FileLogger::class);
// Decorators are built left to right, outer decorators are listed first.
$container->bindDecorators(PostEndpoint::class, [
    LoggingEndpoint::class,
    CachingEndpoint::class,
    BaseEndpoint::class
]);
```
## Tagging

Tagging allows grouping similar implementations for the purpose of referencing them by group.
Grouping implementations makes sense when, as an example, the same method has to be called on each implementation:

```php
use lucatume\DI52\Container;

$container = new Container();

$container->bind(UnsupportedEndpoint::class, function($container){
    $template = '404';
    $message = 'Nope';
    $redirectAfter = 3;
    $redirectTo = $container->get(HomeEndpoint::class);

    return new UnsupportedEndpoint($template, $message, $redirectAfter, $redirectTo);
});

$container->tag([
    HomeEndpoint::class,
    PostEndpoint::class,
    UnsupportedEndpoint::class,
    ], 'endpoints');

foreach($container->tagged('endpoints') as $endpoint) {
    $endpoint->register();
}
```

The `tag` method supports any possibility offered by the container in terms of binding of objects, closures, decorator
chains and after-build methods.

## The callback method

Some applications require callbacks (or some form of callable) to be returned in specific pieces of code.
This is especially the case with WordPress and
its [event-based architecture](https://codex.wordpress.org/Plugin_API/Filter_Reference "Plugin API/Filter Reference « WordPress Codex")
.
Using the container does not removes that possibility:

```php
use lucatume\DI52\Container;

$container = new Container();

add_filter('some_filter', [$container->get(SomeFilteringClass::class), 'filter']);
```

This code suffers from an eager instantiation problem: `SomeFilteringClass` is built for the purpose of binding it but
might never be used.
The problem is easy to solve using the `Container::callback` method:

```php
use lucatume\DI52\Container;

$container = new Container();
$container->singleton(SomeFilteringClass::class);

add_filter('some_filter', $container->callback(SomeFilteringClass::class, 'filter'));
```

The advantage of this solution is the container will return the same callback every time it's called with the same
arguments when the called class is a singleton:

```php
// Some code later we need to remove the filter: we'll get the same callback.
remove_filter('some_filter', App::callback(SomeFilteringClass::class, 'filter'));
```

## Service providers

To avoid passing the container instance around (
see [Service Locator pattern](https://en.wikipedia.org/wiki/Service_locator_pattern "Service locator pattern - Wikipedia"))
or globalising it all the binding should happen in the same PHP file: this could lead, as the application grows, to a
thousand lines monster.
To avoid that the container supports service providers: those are classes extending
the `lucatume\DI52\ServiceProvider` class, that
allow organizing the binding registrations into logical, self-contained and manageable units:

```php
use lucatume\DI52\ServiceProvider;

// file ProviderOne.php
class ProviderOne extends ServiceProvider {
    public function register() {
        $this->container->bind(InterfaceOne::class, ClassOne::class);
        $this->container->bind(InterfaceTwo::class, ClassTwo::class);
        $this->container->singleton(InterfaceThree::class, ClassThree::class);
    }
}

// Application bootstrap file.
use lucatume\DI52\Container;

$container = new Container();

$container->register(ProviderOne::class);
$container->register(ProviderTwo::class);
$container->register(ProviderThree::class);
$container->register(ProviderFour::class);
```

### Booting service providers

The container implements a `boot` method that will, in turn, call the `boot` method on any service provider that
overloads it.
Some applications might define constants and environment variables at "boot" time (e.g. WordPress `plugins_loaded`
action) that might make an immediate registration futile.
In that case service providers can overload the `boot` method:

```php
// file ProviderOne.php

use lucatume\DI52\ServiceProvider;

class ProviderOne extends ServiceProvider {
    public function register() {
        $this->container->bind(InterfaceOne::class, ClassOne::class);
        $this->container->bind(InterfaceTwo::class, ClassTwo::class);
        $this->container->singleton(InterfaceThree::class, ClassThree::class);
    }

    public function boot() {
        if(defined('SOME_CONSTANT')) {
            $this->container->bind(InterfaceFour::class, ClassFour::class);
        } else {
            $this->container->bind(InterfaceFour::class, AnotherClassFour::class);
        }
    }
}

// Application bootstrap file.
use lucatume\DI52\Container;

$container = new Container();

$container->register(ProviderOne::class);
$container->register(ProviderTwo::class);
$container->register(ProviderThree::class);

// Some code later ...
$container->boot();
```

### Deferred service providers

Sometimes even just setting up the implementations might require such an up-front cost to make it undesirable unless
it's needed.
This might happen with non-autoloading code that will require a tangle of files to load (and side load) to grab a simple
class instance.
To "defer" that cost service providers can overload the `deferred` property and the `provides` method:

```php
// file ProviderOne.php

use lucatume\DI52\ServiceProvider;

class ProviderOne extends ServiceProvider {
    public $deferred = true;

    public function provides() {
        return array(LegacyClassOne::class, LegacyInterfaceTwo::class);
    }

    public function register() {
        include_once('legacy-file-one.php')
        include_once('legacy-file-two.php')

        $db = new Db();

        $details = $db->getDetails();

        $this->container->singleton(LegacyClassOne::class, new LegacyClassOne($details));
        $this->container->bind(LegacyInterfaceTwo::class, new LegacyClassTwo($details));
    }
}

// Application bootstrap file
use lucatume\DI52\Container;

$container = new Container();

// The provider `register` method will not be called immediately...
$container->register(ProviderOne::class);

// ...it will be called here as it provides the binding of `LegacyClassOne`
$legacyOne = $container->get(LegacyClassOne::class);

// Will not be called again here, done already.
$legacyTwo = $container->get(LegacyInterfaceTwo::class);
```

### Dependency injection with service providers

The container supports additional dependency injection for service providers (version 3.0.3+). Auto-wiring
will work the same as any class, simply override the service provider's constructor and add any additional concrete dependencies (don't forget to call the parent!):

```php
// file ProviderOne.php

use lucatume\DI52\ServiceProvider;

class ProviderOne extends ServiceProvider {

    /**
     * @var ConfigHelper
     */
    protected $config;

    public function __construct(\lucatume\DI52\Container $container, ConfigHelper $config)
    {
        parent::__construct($container);

        $this->config = $config;
    }

    public function register()
    {
        $this->container->when(ClassFour::class)
            ->needs('$value')
            ->give($this->config->get('value'));
    }

}

// Application bootstrap file.
use lucatume\DI52\Container;

$container = new Container();

$container->register(ProviderOne::class);
```
If you want to inject primitives into a service provider, you need to utilize the `when`, `needs`, `give` methods **_before_** registering the provider in the container:

```php
// file ProviderOne.php

use lucatume\DI52\ServiceProvider;

class ProviderOne extends ServiceProvider {

    /**
     * @var bool
     */
    protected $service_enabled;

    public function __construct(\lucatume\DI52\Container $container, $service_enabled)
    {
        parent::__construct($container);

        $this->service_enabled = $service_enabled;
    }

    public function register()
    {
        if (!$this->service_enabled) {
            return;
        }

        $this->container->bind(InterfaceOne::class, ClassOne::class);
    }

}

// Application bootstrap file.
use lucatume\DI52\Container;

$container = new Container();

$container->when(ProviderOne::class)
    ->needs('$service_enabled')
    ->give(true);

$container->register(ProviderOne::class);
```

## Customizing the container

The container will be built with some opinionated defaults; those are not set in stone and you can customize the
container to your needs.

### Unbound classes resolution
The container will use reflection to work out the dependencies of an object, and will not require setup when resolving
objects with type-hinted object dependencies in the `__construct` method.
By default those _unbound_ classes will be resolved **as prototypes**, built new on **each** `get` request.

To control the mode used to resolve unbound classes, a flag property can be set on the container when constructing it:

```php
use lucatume\DI52\Container;

$container1 = new Container();
$container2 = new Container(true);

// Default resolution of unbound classes is prototype.
assert($container1->get(A::class) !== $container1->get(A::class));
// The second container will resolve unbound classes once, then store them as singletons.
assert($container2->get(A::class) === $container2->get(A::class));
```

This will only apply to unbound classes! Whatever the flag used to build the container instance, the mode set in the
binding phase using `Container::bind()` or `Container::singleton()` methods will **always** be respected.

### Exception masking

By default the container will catch any exception thrown during a service resolution and wrap into a `ContainerException`
instance.  
The container will modify the exception message and the trace file and line to provide information about the nested
resolution tree and point your debug to the file and line that caused the issue.  
You can customize how the container will handle exceptions by using the `Container::setExceptionMask()` method:

```php
use lucatume\DI52\Container;

$container = new Container();

// The container will throw any exception thrown during a service resolution without any modification.
$container->setExceptionMask(Container::EXCEPTION_MASK_NONE);

// Wrap any exception thrown during a service resolution in a `ContainerException` instance, modify the message.
$container->setExceptionMask(Container::EXCEPTION_MASK_MESSAGE);

// Wrap any exception thrown during a service resolution in a `ContainerException` instance, modify the trace file and line.
$container->setExceptionMask(Container::EXCEPTION_MASK_FILE_LINE);

// You can combine the options, this is the default value.
$container->setExceptionMask(Container::EXCEPTION_MASK_MESSAGE | Container::EXCEPTION_MASK_FILE_LINE);
```
