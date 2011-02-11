<?php

namespace Application\AgentBundle;

class AgentBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
	public function getNamespace()
	{
		return __NAMESPACE__;
	}

	public function getPath()
	{
		return __DIR__;
	}
}
