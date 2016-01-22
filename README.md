A PHP 5.2 compatible dependency injection container.

## Installation
Use [Composer](https://getcomposer.org/) to require the library:

```bash
composer require lucatume/di52
```

## Code example
    // ClassOne.php
    class ClassOne implements InterfaceOne {}
    
     // ClassTwo.php
    class ClassTwo implements InterfaceTwo {}
    
     // ClassThree.php
    class ClassThree {
        public function __construct(InterfaceOne $one, InterfaceTwo $two, $arg1 = 'foo', $arg2 = 23){}
    }
    
    // in the pluigin or theme bootstrap file
    
    $container = new tad_DI52_Container();
    
    $container['InterfaceOne'] = 'ClassOne';
    $container['InterfaceTwo'] = 'ClassTwo';
    
    $three = $container['ClassThree'];


    // Using the previous notation
    
    $container['one']  = 'ClassOne';
    $container['two'] = 'ClassTwo';

    $three = $container['three'] = array('ClassThree', '@one', '@two', 'foo', 23);
    
## Usage - object API

### Setting and retrieving variables
In the instance that the need for a shared variable arises the container allows for easy storing and retrieving of variables of any type:

    $c = new tad_DI52_Container();

    $c->set_var('someVar', 'foo');
    
    // prints 'foo'
    print($c->get_var('someVar'));

The opinionated path the container takes about variables, and objects as well, is that those should be set once and later modification will not be allowed; parametrized arguments can be used for that

    $c = new tad_DI52_Container();

    $c->set_var('someVar', 'foo');
    
    // prints 'foo'
    print($c->get_var('someVar'));

    $c->set_var('someVar', 'bar');

    // prints 'foo'
    print($c->get_var('someVar'));

### Setting and getting constructor methods
The container is a dumb one not taking any guess about what an object requires for its instantiation and assuming all the needed args are supplied. This means that concrete class names and methods must be supplied to it, along with needed arguments, to make it work. 
The most basic constructor registration for a class like 

    class SomeClass {
        
        public $one;
        public $two;

        public function __construct(){
            $this->one = new One();
            $this->two = 'foo';
    }

its contstructor can be set in the container like this

    $c = new tad_DI52_Container();
    
    $c->set_ctor('some class', 'SomeClass');

    $someClass = $c->make('some class');
    
    // prints 'foo';
    print($someClass->two);

But a dependency injection container is made to avoid such code in the first place and a rewritten `SomeClass` reads like

    class SomeClass {
        
        public $one;
        public $two;

        public function __construct(One $one, $two){
            $this->one = $one;
            $this->two = $two;
    }

and *might* take advantage of the container like this

    $c = new tad_DI52_Container();
    
    $c->set_ctor('some class', 'SomeClass', new One(), 'foo');

    $someClass1 = $c->make('some class');
    $someClass2 = $c->make('some class');
    
    // prints 'foo';
    print($someClass1->two);

    // not same instance of SomeClass
    $someClass1 !== $someClass2;

    // but shared same instance of One
    $someClass1->one === $someClass2->one;

but the same instance of `One` will be shared between all instances of `SomeClass`.

### Referring registered variables and constructors
The possibility to refer previously registered variables and constructors exists using some special markers for the constructor arguments; given the same class above the code is rewritten to

    $c = new tad_DI52_Container();

    $c->set_ctor('one', 'One');
    $c->set_var('string', 'foo');

    $c->set_ctor('some class', 'SomeClass', '@one', '#string');

    $someClass1 = $c->make('some class');
    $someClass2 = $c->make('some class');
    
    // prints 'foo';
    print($someClass1->two);

    // not same instance of SomeClass
    $someClass1 !== $someClass2;

    // not same instance of One
    $someClass1->one !== $someClass2->one;

### Specifying static constructor methods 
If a class instance should be created using a static constructor as in the case below

    class AnotherClass {

        public $one;
        public $two;

        public static function one(One $one, $two){
            $i = new self;

            $i->one = $one;
            $i->two = $two;

            return $i;
        }
    }

then the registration of the class constructor in the container is possible appending the static method name to the class name like this

    $c = new tad_DI52_Container();

    $c->set_ctor('one', 'One');
    $c->set_var('string', 'foo');

    $c->set_ctor('another class', 'AnotherClass::one', '@one', '#string');

    $anotherClass = $c->make('another class');

### Calling further methods
There might be the need to call some further methods on the instance after it has been created, the container allows for that

    $c = new tad_DI52_Container();

    $c->set_ctor('one', 'One');
    $c->set_var('string', 'foo');

    $c->set_ctor('still another class', 'StillAnotherClass')
        ->setOne('@one')
        ->setString('#string');

    $anotherClass = $c->make('still another class');

    // the same as calling
    $one = new One();
    $string = 'foo';

    $i = new StillAnotherClass();
    $i->setOne($one);
    $i->setString($string);

If the method to call is *covered* by the container methods or there is the desire for a more explicit interface then the `call_method` method can be used; in the example above

    $c->set_ctor('still another class', 'StillAnotherClass')
        ->call_method('setOne', '@one')
        ->call_method('setString', '#string');

### Singleton
Singleton is a notorious and nefarious anti-pattern (and a testing sworn enemy) and the container allows for *sharing* of the same object instance across any call to the `make` method like this

    $c = new tad_DI52_Container();

    $c->set_shared('singleton', 'NotASingleton');

    $i1 = $c->make('singleton');
    $i2 = $c->make('singleton');

    $i1 === $i2;

Shared instances can be referred in other registered constructors using the `@` as well.

## Usage - array API
The array access API leaves some of the flexibility of the object API behind to make some operations quicker.  
Any instance set using the array access API will be a shared one, the code below is equivalent

    $c = new tad_DI52_Container();

    $c['some-class'] = 'SomeClass';

    // is the same as

    $c->set_shared('some-class','SomeClass');

The same syntax is available for variables too

    $c['some-var'] = 'some string';

    // is the same as

    $c->set_var('some-var','some string');

on the same page more complex constructors can be set

    $c->set_shared('some-class', 'SomeClas::instance', 'one', 23);

    // is the same as

    $c['some-class'] = array('SomeClas::instance', 'one', 23);

Getting hold of a shared object instance or a var follows the expected path

    $someClass = $c['some-class'];
    $someVar = $c['some-var'];

Finally registered constructors and variables can be referenced later in other registered constructors

    $c['some-dependency'] = 'DependencyClass';
    $c['some-var'] = 'foo';
    $c['some-class'] = array('SomeClass', '@some-dependency', '#some-var');

#### Alternative notation for variables
Variables can be indicated using the `%varName%` notation as an alternative to the `#varName` one; the example above could be rewritten like this

    $c['some-dependency'] = 'DependencyClass';
    $c['some-var'] = 'foo';
    $c['some-class'] = array('SomeClass', '@some-dependency', '%some-var%');

## Array resolution    
Should a list of container instantiated objects or values be needed the container will allow for that and will properly resolve; using the Array Access API

        $container = new DI();
        $container['ctor-one'] = 'ClassOne';
        $container['ctor-two'] = 'ClassTwo';
        $container['var-one'] = 'foo';
        $container['var-two'] = 'baz';

        $container['a-list-of-stuff'] = array('@ctor-one', '@ctor-two', '#var-one', '#var-two', 'just a string', 23);

        $this->assertInternalType('array', $container['a-list-of-stuff']);
        $this->assertInstanceOf('ClassOne', $container['a-list-of-stuff'][0]);
        $this->assertInstanceOf('ClassTwo', $container['a-list-of-stuff'][1]);
        $this->assertEquals('foo', $container['a-list-of-stuff'][2]);
        $this->assertEquals('baz', $container['a-list-of-stuff'][3]);
        $this->assertEquals('just a string', $container['a-list-of-stuff'][4]);
        $this->assertEquals(23, $container['a-list-of-stuff'][5]);

This can only be done using the array access API.

## Does not support
The container will not guess dependencies, will not handle circular references and will not, in general, make anything smart. Yet.
