# Change Log

All notable changes to this project will be documented in this file. This project adheres
to [Semantic Versioning](http://semver.org/).

## [unreleased] Unreleased

## [3.3.5] 2023-09-01;

### Changed

- Added [PHPStan generics](https://phpstan.org/blog/generics-in-php-using-phpdocs) to multiple classes. IDEs that support them will get autocompletion (.phpstorm.meta.php not required) if a fully-qualified class name is used, e.g. `$instance = $container->get( Test::class );`. 

## [3.3.4] 2023-06-20;

### Added

- Implement the `Container::__clone` method to clone the container accessory classes correctly upon cloning.

### Changed

- The `Container::__construct` method will bind itself to the `Psr\Container\ContainerInterface` interface as a singleton.
- If the `Container` class is extended, it will bind itself to the extended class in the `__construct` and `__clone` methods.

## [3.3.3] 2023-04-28;

### Fixed

- Return value of the `App::callback` method to be `callable` (thanks @estevao90).

## [3.3.2] 2023-04-07;

### Fixed

- Set the correct return type for the `Container::callback` function (thanks @estevao90).

## [3.3.1] 2023-03-17;

### Fixed

- Allow bound interfaces to properly resolve their concrete class when the concrete class has contextual bindings (thanks @defunctl). e.g. the following will now work properly:

```php
$container = new \lucatume\DI52\Container();
$container->bind(SomeInterface::class, SomeClass::class);
$container->when(Someclass:class)
    ->needs('$num')
    ->give(20);

// This now works, properly resolving SomeClass::class.
$instance = $container->get(SomeInterface::class);
```

## [3.3.0] 2023-02-28;

### Breaking change

- Removed the `aliases.php` file from the package autoloading. Take a look at the README.md file to see how to gracefully upgrade to version 3.3

## [3.2.1] 2023-02-28;

### Fixed

- Correctly resolve unbound parameters to default value when available.

## [3.2.0] 2023-02-27;

### Changed

- Allow customization of rethrown exceptions catpured during container resolution; alter message, file and line by default.

### Fixed

- Some `App` generated methods signatures.

## [3.1.1] 2023-02-16;

### Changed
- Removed leftover `@since TBD` comments.

## [3.1.0] 2023-01-28;

### Added
- PHP8.2 support.
- Parameter detection support for PHP Union Types.
- Parameter Enum detection/Enum container resolving.
- thanks @defunctl.

### Changed
- Add PHP8.2 to GitHub workflow matrix.
- Separated tests into a `unit` suite and a `php81` suite to avoid fatal parse errors when asserting enums.
- Updated GitHub workflows to remove deprecated functionality and run composer install via https://github.com/ramsey/composer-install.
- Updated GitHub workflows to attempt to automatically migrate the phpunit configuration file based on the current version being run.
- Updated deprecated "actions/checkout" GitHub action from v2 to v3.
- thanks @defunctl.

### Fixed
- Use the correct PHP version ID to ensure `PHP81ContextualBindingContainerTest` runs under PHP8.1.
- Fixed the phpunit.xml schema to validate against phpunit 5.7.
- Fatal Error Handling snapshots.
- thanks @defunctl.


## [3.0.3] 2023-01-24;

### Added
- The container now registers Service Providers using its own `Container::get()` method, instead of the `new` keyword. This allows Service Providers to utilize dependency injection. (thanks @defunctl).
- Additional contextual binding examples for primitives + service provider documentation in the README.

## [3.0.2] 2023-01-20;

### Added
- Add support for resolving primitive values (e.g. int, string, bool etc...) using `Container::when()`, `Container::needs()` and `Container::give()` (thanks @defunctl).

## [3.0.1] 2022-11-16;

### Changed
- Add `.gitattributes` file to exclude development artifacts. (thanks @Luc45)

## [3.0.0] 2022-02-09;

### Changed
- Add support for a default value in the `lucatume\DI52\Container::getVar(string $key, mixed $default = null)
:mixed` method.
- The `lucatume\DI52\Container::setVar(string $key, mixed $value) :void` will **not** try to run callables when
storing variables on the container using the method. As such, the need to protect the variables when using
the `setVar()` method is no more required.
- The `lucatume\DI52\Container::tagged(string $tag) :array` method will now return an empty array if nothing was
tagged with the tag; it would throw an error in previous versions.
- Rewritten the code to fully leverage Closure support.
- Move build tools to Docker.
- Make the container implementation compatible with [PSR-11 Container specification](https://www.php-fig.
org/psr/psr-11/)
- Fix #26 to handle and format files syntax errors while trying to autoload.
- Fix #13 and allow explicit definition of default binding method.
- Allow getting the registered provider instances using `getProvider` and `get`, `make` or the `ArrayAccess` API.
- Allow getting callbacks for static and instance methods consistently to unhook.
- Add phstan, phan and phpcs checks.
- Move benchmarks to Docker, automate them.
- Update documentation and examples.

### Removed
- Removed the `tad_DI52_ServiceProviderInterface` and `tad_DI52_ContainerInterface` interfaces.

## [2.1.5] 2021-12-20;

### Fixed
- PHP 8.1 compatibility issues (thanks @bordoni)

## [2.1.4] 2021-01-01;

### Fixed
- PHP 8 compatibility issues (thanks @bordoni)

## [2.1.3] 2020-11-02;

### Fixed

- Error messages and format in the context of nested `make` resolution (thanks @Luc45)

## [2.1.2] 2020-10-27;

### Fixed

- PHP 5.3 and 7.4 incompatibility issues

### Changed

- moved the builds to GitHub Actions

## [2.1.1] 2020-10-23;

### Added

- new build tools to the repository

### Changed

- refactor `Container::callback` code to re-use callbacks when available (thanks @sc0ttkclark)

### Fixed

- fix an issue where the Closure produced by the `callback` method would build the object for static method calls

## [2.1.0] - 2020-07-14

### Added

- support for one parameter singletong binding of concrete, instantiatable, classes, thanks @Luc45

## [2.0.12] - 2019-10-14

### Added

- add PHPStorm make method auto-completion (thanks @Luc45)

## [2.0.11] - 2019-09-26

### Changed

- improve exception throwing to show original exceptions when the building of a bound class, interface or slug fails

## [2.0.10] - 2018-10-29

### Fixed

- an issue with array variable handling
- made error message more clear for non-string offsets

## [2.0.9] - 2017-09-26

### Fixed

- issue with `setVar` method where, in some instances, variable values could not be overridden

## [2.0.8] - 2017-07-18

### Fixed

- check for file existence in autoload script (thanks @truongwp)

## [2.0.7] - 2017-06-15

### Fixed

- issue where non registered classes object dependencies would be built just the first time (issue #2)

## [2.0.6] - 2017-05-09

### Fixed

- fix handling of unbound interface arguments

## [2.0.5] - 2017-02-22

### Changed

- change internal method visibility to improve compatibility with monkey patching libraries

## [2.0.4] - 2017-02-22

### Fixed

- allow unbound classes with `__construct` method requirements to be used in `instance` callbacks

## [2.0.3] - 2017-02-07

### Fixed

- support for use of `callback` to feed `instance` and viceversa

## [2.0.2] - 2017-02-02

### Fixed

- support for built objects in `instance` and `callback` methods

## [2.0.1] - 2017-01-23

### Fixed

- an issue where re-binding implementations could lead to built objects still using previous bindings

#### Changed

- removed some dead code left over from previous iterations

## [2.0.0] - 2017-01-21

### Added

- `instance` and `callback` methods

### Changed

- refactored the code completely
- the README file to update it to the new code

### Removed

- support for array based construction instructions (see `instance` methods)

## [1.4.5] - 2017-01-19

### Fixed

- an issue where singleton resolution would result in circular reference on some Windows versions (thanks @bordoni)

## [1.4.4] - 2017-01-09

### Added

- support for binding replacement

## [1.4.3] - 2016-10-18

### Changed

- snake_case method names are now set to camelCase

### Fixed

- an inheritance issue on PHP 5.2
- non PHP 5.2 compatible tests

### Added

- Travis CI support and build

## [1.4.2] - 2016-10-14

### Fixed

- nested dependency resolution issue with interfaces and default values

## [1.4.1b] - 2016-10-14

### Fixed

- pass the `afterBuildMethods` argument along...

## [1.4.1] - 2016-10-14

### Fixed

- updated `tad_di512_Container` `bind` and `singleton` methods signatures

## [1.4.0] - 2016-10-14

### Added

- more informative exception message when trying to resolve unbound slug or non existing class
- support for after build methods

### Fixed

- another nested dependency resolving issue

## [1.3.2] - 2016-07-28

### Fixed

- nested dependency resolving issue

## [1.3.1] - 2016-04-19

### Added

- more informative exception message when default primitive value is missing

## [1.3.0] - 2016-04-19

### Added

- support for the custom bindings
- support for same class singleton binding

### Changed

- performance optimization

## [1.2.6] - 2016-04-11

### Changed

- internal workings to improve performance (
  using [@TomBZombie benchmarks](https://github.com/TomBZombie/php-dependency-injection-benchmarks)

## [1.2.5] - 2016-03-06

### Added

- support for decorator pattern in PHP 5.2 compatible syntax
- code highlighting for code examples in doc (thanks @omarreiss)

## [1.2.4] - 2016-03-05

### Added

- tests for uncovered code

## [1.2.3] - 2016-03-04

### Fixed

- singleton resolution for same implementations

## [1.2.2] - 2016-02-13

- doc updates

## [1.2.1] - 2016-02-13

### Added

- `hasTag($tag)` method to the container
- `isBound($classOrInterface)` method to the container
- support for deferred service providers

## [1.2.1] - 2016-01-23

### Added

- tagging support
- service providers support

## [1.2.0] - 2016-01-22

### Added

- the binding and automatic resolution
  API ([code inspiration](https://www.ltconsulting.co.uk/automatic-dependency-injection-with-phps-reflection-api/))

## [1.1.2] - 2016-01-19

### Fixed

- resolution for objects in arrays

## [1.1.1] - 2016-01-19

### Added

- support for the `%varName%` variable notation.

## [1.1.0] - 2016-01-18

### Added

- array resolution support for the Array Access API.
- the changelog.

[1.0.2]: https://github.com/lucatume/di52/compare/1.0.1...1.0.2

[1.0.3]: https://github.com/lucatume/di52/compare/1.0.2...1.0.3

[1.1.0]: https://github.com/lucatume/di52/compare/1.0.3...1.1.0

[1.1.1]: https://github.com/lucatume/di52/compare/1.0.3...1.1.2

[1.1.2]: https://github.com/lucatume/di52/compare/1.0.3...1.1.2

[1.2.0]: https://github.com/lucatume/di52/compare/1.1.2...1.2.0

[1.2.0]: https://github.com/lucatume/di52/compare/1.1.2...1.2.0

[1.2.1]: https://github.com/lucatume/di52/compare/1.2.0...1.2.1

[1.2.2]: https://github.com/lucatume/di52/compare/1.2.1...1.2.2

[1.2.3]: https://github.com/lucatume/di52/compare/1.2.2...1.2.3

[1.2.4]: https://github.com/lucatume/di52/compare/1.2.3...1.2.4

[1.2.5]: https://github.com/lucatume/di52/compare/1.2.4...1.2.5

[1.2.6]: https://github.com/lucatume/di52/compare/1.2.5...1.2.6

[1.3.0]: https://github.com/lucatume/di52/compare/1.2.6...1.3.0

[1.3.1]: https://github.com/lucatume/di52/compare/1.3.0...1.3.1

[1.3.2]: https://github.com/lucatume/di52/compare/1.3.1...1.3.2

[1.4.0]: https://github.com/lucatume/di52/compare/1.3.1...1.4.0

[1.4.1]: https://github.com/lucatume/di52/compare/1.4.0...1.4.1

[1.4.1b]: https://github.com/lucatume/di52/compare/1.4.1...1.4.1b

[1.4.2]: https://github.com/lucatume/di52/compare/1.4.1b...1.4.2

[1.4.3]: https://github.com/lucatume/di52/compare/1.4.2...1.4.3

[1.4.4]: https://github.com/lucatume/di52/compare/1.4.3...1.4.4

[1.4.5]: https://github.com/lucatume/di52/compare/1.4.4...1.4.5

[2.0.0]: https://github.com/lucatume/di52/compare/1.4.5...2.0.0

[2.0.1]: https://github.com/lucatume/di52/compare/2.0.0...2.0.1

[2.0.2]: https://github.com/lucatume/di52/compare/2.0.1...2.0.2

[2.0.3]: https://github.com/lucatume/di52/compare/2.0.2...2.0.3

[2.0.4]: https://github.com/lucatume/di52/compare/2.0.3...2.0.4

[2.0.5]: https://github.com/lucatume/di52/compare/2.0.4...2.0.5

[2.0.6]: https://github.com/lucatume/di52/compare/2.0.5...2.0.6

[2.0.7]: https://github.com/lucatume/di52/compare/2.0.6...2.0.7

[2.0.8]: https://github.com/lucatume/di52/compare/2.0.7...2.0.8

[2.0.9]: https://github.com/lucatume/di52/compare/2.0.8...2.0.9

[2.0.10]: https://github.com/lucatume/di52/compare/2.0.9...2.0.10

[2.0.11]: https://github.com/lucatume/di52/compare/2.0.10...2.0.11

[2.0.12]: https://github.com/lucatume/di52/compare/2.0.11...2.0.12

[2.1.0]: https://github.com/lucatume/di52/compare/2.0.12...2.1.0

[2.1.1]: https://github.com/lucatume/di52/compare/2.1.0...2.1.1

[2.1.2]: https://github.com/lucatume/di52/compare/2.1.1...2.1.2

[2.1.3]: https://github.com/lucatume/di52/compare/2.1.2...2.1.3
[2.1.4]: https://github.com/lucatume/di52/compare/2.1.3...2.1.4
[2.1.5]: https://github.com/lucatume/di52/compare/2.1.4...2.1.5
[3.0.0]: https://github.com/lucatume/di52/compare/2.1.5...3.0.0
[3.0.1]: https://github.com/lucatume/di52/compare/3.0.0...3.0.1
[3.0.1]: https://github.com/lucatume/di52/compare/3.0.1...3.0.1
[3.0.2]: https://github.com/lucatume/di52/compare/3.0.1...3.0.2
[3.0.3]: https://github.com/lucatume/di52/compare/3.0.2...3.0.3
[3.1.0]: https://github.com/lucatume/di52/compare/3.0.3...3.1.0
[3.1.1]: https://github.com/lucatume/di52/compare/3.1.0...3.1.1
[3.2.0]: https://github.com/lucatume/di52/compare/3.1.1...3.2.0
[3.2.1]: https://github.com/lucatume/di52/compare/3.2.0...3.2.1
[3.3.0]: https://github.com/lucatume/di52/compare/3.2.1...3.3.0
[3.3.1]: https://github.com/lucatume/di52/compare/3.3.0...3.3.1
[3.3.2]: https://github.com/lucatume/di52/compare/3.3.1...3.3.2
[3.3.3]: https://github.com/lucatume/di52/compare/3.3.2...3.3.3
[3.3.4]: https://github.com/lucatume/di52/compare/3.3.3...3.3.4
[3.3.5]: https://github.com/lucatume/di52/compare/3.3.4...3.3.5
[unreleased]: https://github.com/lucatume/di52/compare/3.3.5...HEAD
