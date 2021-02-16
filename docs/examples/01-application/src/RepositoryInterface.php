<?php
/**
 * ${CARET}
 *
 * @since   TBD
 *
 * @package lucatume\DI52\Example1
 */


namespace lucatume\DI52\Example1;

interface RepositoryInterface
{

    /**
     * Fetches a set of entities from the repository.
     *
     * @param int $limit  The max number of entities to fetch.
     * @param int $offset The offset to start and fetch entities from.
     *
     * @return array<mixed> An array of fetched entities.
     */
    public function fetch($limit = 10, $offset = 0);
}
