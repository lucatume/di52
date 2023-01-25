<?php

use lucatume\DI52\Container;
use lucatume\DI52\NotFoundException;
use lucatume\DI52\ServiceProvider;
use PHPUnit\Framework\TestCase;

class TestProviderOne extends ServiceProvider
{
    public $registered = false;

    public function register()
    {
        $this->registered = true;
    }
}

class TestProviderTwo extends ServiceProvider
{
    public $booted = false;

    public function register()
    {
    }

    public function boot()
    {
        $this->booted = true;
    }
}

class LegacyDbConnection
{
}

class LegacyAlternateDbConnection
{
}

class LegacyDbProvider extends ServiceProvider
{
    public function register()
    {

        $this->container->singleton(LegacyDbConnection::class, static function () {
            return new LegacyDbConnection();
        });
        $this->container->singleton(LegacyAlternateDbConnection::class, static function () {
            return new LegacyAlternateDbConnection();
        });
    }

    public function provides()
    {
        return [ LegacyDbConnection::class, LegacyAlternateDbConnection::class ];
    }
}
class LegacyUserModel
{
}
class UserModelBridge
{
}
class LegacyUserDb
{
}
class LegacyUserProvider extends ServiceProvider
{
    public function register()
    {
        $this->container->bind(LegacyUserModel::class, UserModelBridge::class);
        $this->container->singleton(LegacyUserDb::class, static function () {
            return new LegacyUserDb('user', 'password');
        });
    }

    public function provides()
    {
        return [LegacyUserModel::class,LegacyUserDb::class];
    }
}

class ExternalServiceDataProvider extends ServiceProvider
{
    public function register()
    {
        $this->container['data_1'] = 23;
        $this->container['data_2'] = 89;
    }

    public function provides()
    {
        return ['data_1','data_2'];
    }
}

class AliasServiceProvider extends ServiceProvider
{
    public $registered = false;

    public function register()
    {
        $this->registered = true;
    }
}

class TestDependency
{

    public function getValue()
    {
        return 'test';
    }
}

class TestProviderInjection extends ServiceProvider
{

    /**
     * @var TestDependency
     */
    protected $dependency;

    public function __construct(Container $container, TestDependency $dependency)
    {
        parent::__construct($container);

        $this->dependency = $dependency;
    }

    public function register()
    {
    }

    public function getContainer() {
        return $this->container;
    }

    public function getDependency()
    {
        return $this->dependency;
    }
}

class TestProviderPrimitiveInjection extends ServiceProvider
{

    /**
     * @var string
     */
    protected $value;

    public function __construct(Container $container, $value)
    {
        parent::__construct($container);

        $this->value = $value;
    }

    public function register()
    {
    }

    public function getValue()
    {
        return $this->value;
    }
}

class ServiceProviderTest extends TestCase
{
    /**
     * It should correctly register a service provider
     *
     * @test
     */
    public function should_correctly_register_a_service_provider()
    {
        $container = new Container();

        $container->register(TestProviderOne::class);

        $this->assertTrue($container->getProvider(TestProviderOne::class)->registered);
    }

    /**
     * It should throw if trying ot get not registered service provider
     *
     * @test
     */
    public function should_throw_if_trying_ot_get_not_registered_service_provider()
    {
        $container = new Container();

        $this->expectException(NotFoundException::class);

        $container->getProvider(TestProviderOne::class);
    }

    public function resolveUnboundAsSingletonsDataProvider()
    {
        return [
            'true'  => [ true ],
            'false' => [ false ],
        ];
    }

    /**
     * It should allow getting a registered service provider using make and get API
     *
     * @test
     * @dataProvider  resolveUnboundAsSingletonsDataProvider
     */
    public function should_allow_getting_a_registered_service_provider_using_make_and_get_api($resolveUnboundAsSingletons)
    {
        $container = new Container($resolveUnboundAsSingletons);

        $container->register(TestProviderOne::class);

        $got = $container->get(TestProviderOne::class);
        $this->assertInstanceOf(TestProviderOne::class, $got);
        $made = $container->make(TestProviderOne::class);
        $this->assertInstanceOf(TestProviderOne::class, $made);
        $accessed = $container[TestProviderOne::class];
        $this->assertInstanceOf(TestProviderOne::class, $accessed);
        $this->assertSame($got, $made);
        $this->assertSame($got, $accessed);
        $this->assertSame($made, $accessed);
    }

    /**
     * It should make boot method a callable no-op by default
     *
     * @test
     */
    public function should_make_boot_method_a_callable_no_op_by_default()
    {
        $container = new Container();

        $container->register(TestProviderOne::class);
        $container->register(TestProviderTwo::class);

        $this->assertFalse($container->getProvider(TestProviderTwo::class)->booted);

        $container->boot();

        $this->assertTrue($container->getProvider(TestProviderTwo::class)->booted);
    }

    /**
     * It should correctly register a deferred provider
     *
     * @test
     */
    public function should_correctly_register_a_deferred_provider()
    {
        $container = new Container();

        $container->register(LegacyDbProvider::class);

        $legacyDb = LegacyDbConnection::class;
        $legacyAltDb = LegacyAlternateDbConnection::class;
        $this->assertInstanceOf($legacyDb, $container->make($legacyDb));
        $this->assertSame($container->make($legacyDb), $container->make($legacyDb));
        $this->assertInstanceOf($legacyAltDb, $container->make($legacyAltDb));
        $this->assertSame($container->make($legacyAltDb), $container->make($legacyAltDb));
    }

    /**
     * It should correctly register a deferred provider with mixed binding types
     *
     * @test
     */
    public function should_correctly_register_a_deferred_provider_with_mixed_binding_types()
    {
        $container = new Container();

        $container->register(LegacyUserProvider::class);

        $legacyUser = LegacyUserModel::class;
        $legacyDb   = LegacyUserDb::class;
        $this->assertInstanceOf(UserModelBridge::class, $container->make($legacyUser));
        $this->assertNotSame($container->make($legacyUser), $container->make($legacyUser));
        $this->assertInstanceOf($legacyDb, $container->make($legacyDb));
        $this->assertSame($container->make($legacyDb), $container->make($legacyDb));
    }

    /**
     * It should allow registering deferred providers to provide variables
     *
     * @test
     */
    public function should_allow_registering_deferred_providers_to_provide_variables()
    {
        $container = new Container();

        $container->register(ExternalServiceDataProvider::class);

        $this->assertEquals(23, $container['data_1']);
        $this->assertEquals(89, $container['data_2']);
    }

    /**
     * It should allow registering a service provider with one alias
     *
     * @test
     */
    public function should_allow_registering_a_service_provider_with_one_alias()
    {
        $container = new Container();

        $container->register(AliasServiceProvider::class, 'alias-service-provider');

        $this->assertTrue($container->getProvider(AliasServiceProvider::class)->registered);
        $this->assertSame(
            $container->getProvider(AliasServiceProvider::class),
            $container->getProvider(AliasServiceProvider::class)
        );
        $this->assertSame(
            $container->getProvider(AliasServiceProvider::class),
            $container->getProvider('alias-service-provider')
        );
    }

    /**
     * It should allow registering a service provider with multiple aliases
     *
     * @test
     */
    public function should_allow_registering_a_service_provider_with_multiple_aliases()
    {
        $container = new Container();

        $container->register(
            AliasServiceProvider::class,
            'alias-service-provider',
            'alternate-service-provider',
            'other-service-provider'
        );

        $this->assertTrue($container->getProvider(AliasServiceProvider::class)->registered);
        $this->assertSame(
            $container->getProvider(AliasServiceProvider::class),
            $container->getProvider(AliasServiceProvider::class)
        );
        $this->assertSame(
            $container->getProvider(AliasServiceProvider::class),
            $container->getProvider('alias-service-provider')
        );
        $this->assertSame(
            $container->getProvider(AliasServiceProvider::class),
            $container->getProvider('alternate-service-provider')
        );
        $this->assertSame(
            $container->getProvider(AliasServiceProvider::class),
            $container->getProvider('other-service-provider')
        );
    }

    /**
     * Test we can create service providers with additional concrete dependencies.
     *
     * @test
     */
    public function should_automatically_inject_concrete_instances_into_extended_providers()
    {
        $container = new Container();

        $container->register(TestProviderInjection::class);
        $this->assertTrue($container->has(TestProviderInjection::class));
        $this->assertTrue($container->has(TestDependency::class));

        $provider = $container->get(TestProviderInjection::class);

        $this->assertSame($container, $provider->getContainer());
        $this->assertInstanceOf(TestDependency::class, $provider->getDependency());
        $this->assertSame('test', $provider->getDependency()->getValue());
    }

    /**
     * Test we can configure the container with providers that have primitives
     * in their constructor.
     *
     * @test
     */
    public function should_allow_binding_providers_with_primitive_values()
    {
        $container = new Container();

        $container->when(TestProviderPrimitiveInjection::class)
            ->needs('$value')
            ->give('test');

        $container->register(TestProviderPrimitiveInjection::class);
        $this->assertTrue($container->has(TestProviderPrimitiveInjection::class));
        $this->assertSame('test', $container->get(TestProviderPrimitiveInjection::class)->getValue());
    }
}
