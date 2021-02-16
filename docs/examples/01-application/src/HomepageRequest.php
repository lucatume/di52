<?php
/**
 * Handles a request for the application homepage.
 *
 * @package lucatume\DI52\Example1
 */

namespace lucatume\DI52\Example1;

/**
 * Class HomepageRequest
 *
 * @package lucatume\DI52\Example1
 */
class HomepageRequest extends AbstractRequest
{
    /**
     * {@inheritdoc}
     */
    public function serve()
    {
        $posts = $this->repository->fetch(3, 0);
        echo $this->template->render('public/posts.php', ['posts' => $posts]);
    }
}
