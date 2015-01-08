A PHP 5.2 compatible dependency injection container.

## Installation
Download the library to your project and either require its files or add it to autoloading including the provided `autoload.php` file.

## Usage

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
    
    $args = array(new One(), 'foo');
    $c->set_ctor('some class', 'SomeClass', $args);

    $someClass1 = $c->make('some class');
    $someClass2 = $c->make('some class');
    
    // prints 'foo';
    print($someClass->two);

    // not same instance of SomeClass
    $someClass1 !== $someClass2;

    // but shared same instance of One
    $someClass1->one === $someClass2->one;

but the same instance of `One` will be shared between all instances of `SomeClass`.

### Referring registered variables and constructors
The possibility to refer previously registered variables and constructors exists using some specia markers for the constructor arguments; given the same class above the code is rewritten to

    $c = new tad_DI52_Container();

    $c->set_ctor('one', 'One');
    $c->set_var('string', 'foo');

    $args = array('@one', '#string');
    $c->set_ctor('some class', 'SomeClass', $args);

    $someClass1 = $c->make('some class');
    $someClass2 = $c->make('some class');
    
    // prints 'foo';
    print($someClass->two);

    // not same instance of SomeClass
    $someClass1 !== $someClass2;

    // not same instance of One
    $someClass1->one !== $someClass2->one;

### Specifyin static constructor methods 
If a class instance should be created using a static constructor like in the case below

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

    $args('@one', '#string')
    $c->set_ctor('another class', 'AnotherClass::one', $args);

    $anotherClass = $c->make('another class');

## Does not support
The container will not guess dependencies, will not handle circular references and will not, in general, make anything smart. Yet.