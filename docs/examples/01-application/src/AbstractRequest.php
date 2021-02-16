<?php
/**
 * ${CARET}
 *
 * @since   TBD
 *
 * @package lucatume\DI52\Example1
 */


namespace lucatume\DI52\Example1;

/**
 * Class AbstractRequest
 *
 * @package lucatume\DI52\Example1
 */
abstract class AbstractRequest
{
    /**
     * A reference to the template engine instance the request should use to render its content.
     *
     * @var TemplateInterface
     */
    protected $template;

    /**
     * A reference to the repository instance the request should use to fetch its content.
     *
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * AbstractRequest constructor.
     * @param TemplateInterface   $template   A reference to the template engine the request should
     *                                        use to render its content.
     * @param RepositoryInterface $repository A reference to the repository the request should use
     *                                        to fetch its content.
     */
    public function __construct(TemplateInterface $template, RepositoryInterface $repository)
    {
        $this->template = $template;
        $this->repository = $repository;
    }

    /**
     * Serves the request;
     *
     * @return string The rendered content.
     */
    abstract public function serve();
}
