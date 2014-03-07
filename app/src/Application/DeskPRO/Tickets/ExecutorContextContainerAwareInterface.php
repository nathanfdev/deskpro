<?php
/**
 * Created by PhpStorm.
 * User: chroder
 * Date: 07/03/2014
 * Time: 12:30
 */
namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

interface ExecutorContextContainerAwareInterface extends ExecutorContextInterface
{
	/**
	 * @param $container DeskproContainer
	 */
	public function setContainer(DeskproContainer $container);

	/**
	 * @return DeskproContainer
	 */
	public function getContainer();
}