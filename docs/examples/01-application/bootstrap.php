<?php
/**
 * The application bootstrap file: here the container is provided the minimal set of instructions
 * required to set up the application objects.
 */

namespace lucatume\DI52\Example1;

use lucatume\DI52\App;
use lucatume\DI52\Container;

// Start by building an instance of the DI container.
$container = new Container();

spl_autoload_register(
    static function ($class) {
        if (0 !== strpos($class, __NAMESPACE__)) {
            return false;
        }

        $path = __DIR__ . '/src/' . str_replace(__NAMESPACE__ . '\\', '', $class) . '.php';

        if (!is_file($path)) {
            return false;
        }

        require_once $path;

        return true;
    }
);

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
