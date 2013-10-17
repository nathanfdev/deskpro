<?php

class MockRoute extends \Symfony\Component\Routing\Route
{
	public $arg1 = -1;
	public $arg2 = -1;
	public $arg3 = -1;
	public $arg4 = -1;
	public $arg5 = -1;
	public $arg6 = -1;
	public $arg7 = -1;
	public $arg8 = -1;
	public $arg9 = -1;

	public function __construct()
	{
		$args = func_get_args();
		foreach ($args as $x => $val) {
			$name = 'arg' . ($x+1);
			$this->$name = $val;
		}
	}
}