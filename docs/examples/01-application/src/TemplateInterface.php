<?php
/**
 * The API of any template engine.
 *
 * @package lucatume\DI52\Example1
 */

namespace lucatume\DI52\Example1;

/**
 * Interface TemplateInterface
 *
 * @package lucatume\DI52\Example1
 */
interface TemplateInterface
{
    /**
     * Renders the specified template with the specified data.
     *
     * @param string              $templatePath The template path
     * @param array<string,mixed> $data         The data to extract in the template context.
     *
     * @return string The rendered template.
     */
    public function render($templatePath, array $data = []);
}
