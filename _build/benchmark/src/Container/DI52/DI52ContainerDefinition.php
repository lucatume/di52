<?php
declare(strict_types=1);

namespace DiContainerBenchmarks\Container\DI52;

use DiContainerBenchmarks\Container\ContainerAdapterInterface;
use DiContainerBenchmarks\Container\ContainerDefinitionInterface;

class DI52ContainerDefinition implements ContainerDefinitionInterface {

	public function getPackage(): string {
		return 'lucatume/di52';
	}

	public function getName(): string {
		return 'di52';
	}

	public function getDisplayedName(): string {
		return 'DI52';
	}

	public function isCompiled(): bool {
		return false;
	}

	public function isAutowiringSupported(): bool {
		return true;
	}

	public function getUrl(): string {
		return "https://github.com/lucatume/di52";
	}

	public function getAdapter(): ContainerAdapterInterface {
		return new DI52ContainerAdapter();
	}
}
