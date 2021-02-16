<?php

use lucatume\DI52\Container;

require __DIR__ . '/../vendor/autoload.php';

$appFacadeSourceFile = __DIR__ . '/../src/Container.php';
$appFacadeDestFile = __DIR__ . '/../src/App.php';
$appFacadeSourceFileLines = file($appFacadeSourceFile);

if (false === $appFacadeSourceFileLines) {
    echo "\nCould not open container source file ({$appFacadeSourceFile}).\n";
    exit(1);
}

$extractMethodArgs = static function (ReflectionMethod $method) use ($appFacadeSourceFileLines) {
    $methodLine = $method->getStartLine();
    $signatureLine = $appFacadeSourceFileLines[$methodLine - 1];
    $signatureLineFrags = preg_split('/[()]/', $signatureLine);
    return $signatureLineFrags[1] ?? '';
};

$methodReturnsVoid = static function (ReflectionMethod $method) {
    $docBlock = $method->getDocComment();
    return count(
        array_filter(
            explode("\n", $docBlock),
            static function (string $line) {
                return preg_match('/@return void/', $line);
            }
        )
    );
};

$appFacadeContentsTemplate = <<< PHP_CODE
<?php
/**
 * A facade to make a DI container instance globally available as a Service Locator.
 *
 * @package lucatume\DI52
 */

namespace lucatume\DI52;

/**
 * Class App
 *
 * @package lucatume\DI52
 */
class App
{
    /** A reference to the singleton instance of the DI container
     * the application uses as Service Locator.
     *
     * @var Container|null
     */
    protected static \$container;

    /**
     * Returns the singleton instance of the DI container the application
     * will use as Service Locator.
     *
     * @return Container The singleton instance of the Container used as Service Locator
     *                   by the application.
     */
    public static function container()
    {
        if (!isset(static::\$container)) {
            static::\$container = new Container();
        }

        return static::\$container;
    }
    
    /**
     * Sets the container instance the Application should use as a Service Locator.
     *
     * If the Application already stores a reference to a Container instance, then
     * this will be replaced by the new one.
     * 
     * @param Container \$container A reference to the Container instance the Application
     *                             should use as a Service Locator.
     *
     * @return void The method does not return any value.
     */
    public static function setContainer(Container \$container)
    {
        static::\$container = \$container;
    }
    
    {{ generated_code }}
}
PHP_CODE;
$methodTemplate = <<< PHP_CODE
    {{ method_doc_block }}
    public static function {{ method_name }}({{ method_args }})
    {
        {{ method_return }}static::container()->{{ method_name }}({{ method_arg_names }});
    }
PHP_CODE;

$containerClassReflection = new ReflectionClass(Container::class);
$containerPublicMethods = $containerClassReflection->getMethods(ReflectionMethod::IS_PUBLIC);
$appFacadeGenereatedCodeEntries = [];
foreach ($containerPublicMethods as $method) {
    if ($method->name === '__construct') {
        continue;
    }

    $data = [
        '{{ method_doc_block }}' => (string)$method->getDocComment(),
        '{{ method_return }}' => $methodReturnsVoid($method) ? '' : 'return ',
        '{{ method_name }}' => $method->getName(),
        '{{ method_args }}' => $extractMethodArgs($method),
        '{{ method_arg_names }}' => implode(
            ', ',
            array_map(
                static function (ReflectionParameter $p) {
                    return '$' . $p->getName();
                },
                $method->getParameters()
            )
        )
    ];

    $methodCode = str_replace(array_keys($data), $data, $methodTemplate);
    $appFacadeGenereatedCodeEntries[] = $methodCode;
}
$appFacadeGenereatedCode = implode("\n\n", $appFacadeGenereatedCodeEntries);
$appFacadeContents = str_replace('{{ generated_code }}', $appFacadeGenereatedCode, $appFacadeContentsTemplate);

if(!file_put_contents($appFacadeDestFile, $appFacadeContents)){
    throw new \RuntimeException("Could not create file {$appFacadeDestFile}");
}
