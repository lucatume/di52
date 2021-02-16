<?php
/**
 * The application entrypoint: start PHP built-in web-server with `php -S localhost:8888`
 * and visit `https://localhost:8888` to see this example in action.
 *
 * @package lucatume\DI52\Example1
 */

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
