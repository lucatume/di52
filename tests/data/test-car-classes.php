<?php

class Car {
	public function __construct( Engine $e ) {
	}
}

class Engine {
	public function __construct( UpperEngine $u, LowerEngine $l ) {
	}
}

class UpperEngine {
	public function __construct( CombustionChamber $c ) {
	}
}

class CombustionChamber {
	public function __construct( Valve $v ) {
	}
}

class Valve {
	public function __construct( NonExisting1 $n ) {
	}
}

class LowerEngine {
	public function __construct( DriveShaft $c ) {
	}
}

class DriveShaft {
	public function __construct( Clutch $c ) {
	}
}

class Clutch {
	public function __construct( PrivateConstructor $c ) {
	}
}
