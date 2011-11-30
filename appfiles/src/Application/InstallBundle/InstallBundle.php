<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage InstallBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\InstallBundle;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InstallBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
	public function __construct()
	{
		$this->name = 'Install';
	}

	public function build(ContainerBuilder $container)
    {
        $container->registerExtension(new \Application\InstallBundle\DependencyInjection\InstallExtension());
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
