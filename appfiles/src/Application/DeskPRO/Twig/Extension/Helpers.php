<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Twig
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Twig\Extension;

use \Symfony\Component\Templating\Engine;
use \Symfony\Component\DependencyInjection\ContainerInterface;

class Helpers extends \Twig_Extension
{
	protected $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function getContainer()
	{
		return $this->container;
	}

	public function getTemplating()
	{
		return $this->container->get('templating');
	}

	public function getFunctions()
	{
		return array(
			'phrase'   => new \Twig_Function_Method($this, 'getPhrase'),
		);
	}

	public function getPhrase($name, array $parameters = array())
	{
		return $this->getTemplating()->get('phrase')->phrase($name, $parameters);
	}

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'deskpro.helpers';
    }
}
