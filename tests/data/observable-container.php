<?php

namespace lucatume\DI52;

class ObservableContainer extends Container
{
    public function _resolveParameter(...$args)
    {
        return $this->resolveParameter(...$args);
    }
}
