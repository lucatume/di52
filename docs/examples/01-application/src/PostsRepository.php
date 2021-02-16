<?php
/**
 * A Posts repository that will read posts from a set of files.
 *
 * @since   TBD
 *
 * @package lucatume\DI52\Example1
 */

namespace lucatume\DI52\Example1;

use FilesystemIterator;
use SplFileInfo;

/**
 * Class PostsRepository
 *
 * @since   TBD
 *
 * @package lucatume\DI52\Example1
 */
class PostsRepository implements RepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function fetch($limit = 10, $offset = 0)
    {
        $options = FilesystemIterator::SKIP_DOTS;
        $postsDirIterator = new FilesystemIterator(dirname(__DIR__) . '/posts', $options);
        $postsDirIterator->seek($offset);
        $i = new \CallbackFilterIterator(
            $postsDirIterator,
            static function (SplFileInfo $file) {
                return $file->isFile() && $file->getExtension() === 'html';
            }
        );

        $posts = [];

        /** @var SplFileInfo $file */
        foreach ($i as $file) {
            $contents = file_get_contents($file->getPathname());
            if ($contents === false) {
                throw new \RuntimeException("Cannot read contents of file {$file->getPathname()}");
            }
            $posts[] = [
                'title' => $file->getBasename('.html'),
                'body' => $contents
            ];
        }

        return $posts;
    }
}
