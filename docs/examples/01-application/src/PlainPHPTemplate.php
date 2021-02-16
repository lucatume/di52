<?php
/**
 * A simple PHP-based template engine that will work by extracting variables in
 * the template context.
 *
 * @since   TBD
 *
 * @package lucatume\DI52\Example1
 */

namespace lucatume\DI52\Example1;

/**
 * Class PlainPHPTemplate
 *
 * @package lucatume\DI52\Example1
 */
class PlainPHPTemplate implements TemplateInterface
{
    /**
     * The absolute path to the templates root directory.
     *
     * @var string
     */
    private $templatesDir;

    /**
     * PlainPHPTemplate constructor.
     *
     * @param string $templatesDir The absolute path to the templates root directory.
     */
    public function __construct($templatesDir)
    {
        $this->templatesDir = rtrim($templatesDir, '\\/');
    }

    /**
     * {@inheritdoc}
     */
    public function render($templatePath, array $data = [])
    {
        extract($data, EXTR_OVERWRITE);
        include $this->templatesDir .'/'. trim($templatePath, '\\/');
    }
}
