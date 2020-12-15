<?php

use lucatume\DI52\Container;
use PHPUnit\Framework\TestCase;

interface Animal
{
    public function eat();
}

class Pangolin implements Animal
{
    public function eat()
    {
        return 'ants'  ;
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
}
