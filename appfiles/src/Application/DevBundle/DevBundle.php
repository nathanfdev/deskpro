<?php

namespace Application\DevBundle;

class DevBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
	public function getAlias()
    {
        return 'dpdev';
    }
}
