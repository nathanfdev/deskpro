<?php

namespace Application\DevBundle;

class DevBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
	public function getAlias()
    {
        return 'dpdev';
    }

	public function getNamespace()
	{
		return __NAMESPACE__;
	}

	public function getPath()
	{
		return __DIR__;
	}
}
