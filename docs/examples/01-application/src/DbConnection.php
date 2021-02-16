<?php
/**
 * A database connection.
 *
 * A mock for the purpose of the example.
 *
 * @package lucatume\DI52\Example1
 */

namespace lucatume\DI52\Example1;

/**
 * Class DbConnection
 *
 * @package lucatume\DI52\Example1
 */
class DbConnection
{
    /**
     * A method that will mock a fetch from the database for a set of users.
     *
     * @param int $limit  The max number of users to return.
     * @param int $offset The offset to start and fetch users from.
     *
     * @return array<array<string,int|string>> A set of mock user results.
     */
    public function fetchUsers($limit = 1, $offset = 0)
    {
        // Mock! In reality there should be a db connection here.
        $mocks = [
            ['id' => 1, 'username' => 'Luca'],
            ['id' => 2, 'username' => 'Joe'],
            ['id' => 21, 'username' => 'Bob'],
            ['id' => 23, 'username' => 'Jane'],
            ['id' => 54, 'username' => 'Alice'],
            ['id' => 89, 'username' => 'Lorem'],
            ['id' => 2389, 'username' => 'Dolor'],
            ['id' => 12344, 'username' => 'Sit']
        ];

        return array_slice($mocks, $offset, $limit);
    }
}
