<?php
/**
 * ${CARET}
 *
 * @since   TBD
 *
 * @package lucatume\DI52\Builders
 */


namespace lucatume\DI52\Builders;

interface ReinitializableBuilderInterface
{
    public function reinit(array $afterBuildMethods = null, ...$buildArgs);
}
