<?php
/**
 * Handles a request for the application users list.
 *
 * @package lucatume\DI52\Example1
 */

namespace lucatume\DI52\Example1;

/**
 * Class UsersPageRequest
 *
 * @package lucatume\DI52\Example1
 */
class UsersPageRequest extends AbstractRequest
{
    public function serve()
    {
        $posts = $this->repository->fetch(5, 1);
        echo $this->template->render('public/users.php', ['posts' => $posts]);
    }
}
