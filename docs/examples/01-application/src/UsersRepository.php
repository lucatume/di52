<?php
/**
 * Handles Users by means of a database connection.
 *
 * @package lucatume\DI52\Example1
 */

namespace lucatume\DI52\Example1;

/**
 * Class UsersRepository
 *
 * @package lucatume\DI52\Example1
 */
class UsersRepository implements RepositoryInterface
{
    /**
     * @var DbConnection
     */
    private $dbConnection;

    /**
     * UsersRepository constructor.
     *
     * @param DbConnection $dbConnection A reference to the Application database
     *                                   connection.
     */
    public function __construct(DbConnection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($limit = 10, $offset = 0)
    {
        return $this->dbConnection->fetchUsers($limit, $offset);
    }
}
