<?php
declare(strict_types=1);

namespace DiContainerBenchmarks\Container\DI52;

use DiContainerBenchmarks\Container\ContainerAdapterInterface;
use lucatume\DI52\Container;

final class DI52ContainerAdapter implements ContainerAdapterInterface
{

    public function build(): void
    {
    }

    public function bootstrapSingletonContainer()
    {
        return new Container(true);
    }

    public function bootstrapPrototypeContainer()
    {
        return new Container(false);
    }
}
