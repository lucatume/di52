<?php

use lucatume\DI52\Container;
use PHPUnit\Framework\TestCase;

interface Animal
{
    public function eat();

    public function legs();
}

class Pangolin implements Animal
{
    public function eat()
    {
        return 'ants';
    }

    public function legs()
    {
        return 4;
    }
}

class Omnivore implements Animal
{
    private $eats;
    private $legs;

    public function __construct($eats, $legs)
    {
        $this->eats = $eats;
        $this->legs = $legs;
    }

    public function eat()
    {
        return $this->eats;
    }

    public function legs()
    {
        return $this->legs;
    }
}

class Blorb implements Animal
{
    private $eats;
    private $legs;

    public function setEats()
    {
        $this->eats = 'fzorps';
    }

    public function setLegs()
    {
        $this->legs = 2;
    }

    public function eat()
    {
        return $this->eats;
    }

    public function legs()
    {
        return $this->legs;
    }
}

class Choomba implements Animal
{

    protected $eats;
    protected $legs;

    public function __construct($eats, $legs)
    {
        $this->eats = $eats;
        $this->legs = $legs;
    }

    public function setEats()
    {
        $this->eats = 'boxes';
    }

    public function setLegs()
    {
        $this->legs = 5;
    }

    public function eat()
    {
        return $this->eats;
    }

    public function legs()
    {
        return $this->legs;
    }
}

class Shoe
{
    private $size;
    private $color;

    public function __construct($size = 40, $color = 'black')
    {
        $this->size = $size;
        $this->color = $color;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setLargeSize()
    {
       $this->size = 50 ;
    }

    public function setFunColor()
    {
        $this->color = 'green';
    }
}

class ShoeFactory
{
    public static function produce($size = 40, $color = 'black')
    {
        return new Shoe($size,$color);
    }

    public function __invoke(...$args)
    {
       return static::produce(...$args);
    }
}

class InstanceTest extends TestCase
{
    /**
     * It should allow building an instance for a bound class
     *
     * @test
     */
    public function should_allow_building_an_instance_for_a_bound_class()
    {
        $container = new Container();

        $container->bind(Animal::class, Pangolin::class);

        $animalEat = $container->instance(Animal::class);

        $this->assertEquals('ants', $animalEat()->eat());
    }

    /**
     * It should allow building an instance for an unbound class
     *
     * @test
     */
    public function should_allow_building_an_instance_for_an_unbound_class()
    {
        $container = new Container();

        $animalEat = $container->instance(Pangolin::class);

        $this->assertEquals('ants', $animalEat()->eat());
    }

    /**
     * It should allow building an instance with build args
     *
     * @test
     */
    public function should_allow_building_an_instance_with_build_args()
    {
        $container = new Container();

        $container->bind(Animal::class, Omnivore::class);
        $animal = $container->instance(Animal::class, ['carrots', 5]);

        $this->assertEquals('carrots', $animal()->eat());
        $this->assertEquals(5, $animal()->legs());
    }

    /**
     * It should allow building an instance with after setup methods
     *
     * @test
     */
    public function should_allow_building_an_instance_with_after_setup_methods()
    {
        $container = new Container();

        $container->bind(Animal::class, Blorb::class);
        $blorb = $container->instance(Animal::class, [], ['setEats', 'setLegs']);

        $this->assertEquals('fzorps', $blorb()->eat());
        $this->assertEquals(2, $blorb()->legs());
    }

    /**
     * It should allow building an instance with build args and after setup methods
     *
     * @test
     */
    public function should_allow_building_an_instance_with_build_args_and_after_setup_methods()
    {
        $container = new Container();

        $container->bind(Animal::class, Choomba::class);
        $choomba = $container->instance(Animal::class, ['other choombas', 7], ['setEats', 'setLegs']);

        $this->assertEquals('boxes', $choomba()->eat());
        $this->assertEquals(5, $choomba()->legs());
    }

    /**
     * It should allow building instance for callable array
     *
     * @test
     */
    public function should_allow_building_instance_for_callable_array()
    {
        $container = new Container();

        $container->bind('shoe', [ShoeFactory::class, 'produce']);
        $instance = $container->instance('shoe');

        $shoe = $instance();
        $this->assertInstanceOf(Shoe::class, $shoe);
        $this->assertEquals(40, $shoe->getSize());
        $this->assertEquals('black', $shoe->getColor());
    }

    /**
     * It should allow building an instance with build args and callable builder
     *
     * @test
     */
    public function should_allow_building_an_instance_with_build_args_and_callable_builder()
    {
        $container = new Container();

        $container->bind('shoe', [ShoeFactory::class, 'produce']);
        $produce = $container->instance('shoe', [42, 'brown']);

        $shoe = $produce();

        $this->assertInstanceOf(Shoe::class, $shoe);
        $this->assertEquals(42, $shoe->getSize());
        $this->assertEquals('brown', $shoe->getColor());
    }

    /**
     * It should allow building an instance with after build methods and callable builder
     *
     * @test
     */
    public function should_allow_building_an_instance_with_after_build_methods_and_callable_builder()
    {
        $container = new Container();

        $container->bind('shoe', new ShoeFactory());
        $produce = $container->instance('shoe', [42, 'brown'],['setLargeSize','setFunColor']);

        $shoe = $produce();

        $this->assertInstanceOf(Shoe::class, $shoe);
        $this->assertEquals(50, $shoe->getSize());
        $this->assertEquals('green', $shoe->getColor());
    }
}
