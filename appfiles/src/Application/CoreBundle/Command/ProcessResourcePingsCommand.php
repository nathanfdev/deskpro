<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage CoreBundle
 * @category Commands
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;



/**
 * dp:process-resource-pings
 *
 * Uses database info from config and drops/re-creates the database, and then inspects
 * all entities to generate a fresh schema.
 */
class ProcessResourcePingsCommand extends \Symfony\Bundle\FrameworkBundle\Command\Command
{
	protected function configure()
	{
		$this->setName('dp:process-resource-pings');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$em = $this->container->get('doctrine.orm.entity_manager');

		/* @var $queue DeskPRO\Queue\Queue */
		$queue = $this->container->get('deskpro.core.queue_factory')->createForQueue('object_updated');

		while ($message = $queue->receive()) {
			list($resource_id, $record_id) = explode(':', $message->body, 2);

			try {
				$r_resource = $em->find('CoreBundle:RemoteResource', $resource_id);
				$r_record   = $em->find('CoreBundle:RemoteRecord', $record_id);
			} catch (\Doctrine\ORM\NoResultException $e) {
				// Means there is no resource or record with those ID's
				// TODO: We should log this
				$queue->deleteMessage($message);
				continue;
			}


			$scraper = $r_resource->getScraper();
			$item = $scraper->getData($record_id);

			$r_record->fromScraperItem($item);
			$em->persist($r_record);

			$r_resource->notifyListeners($r_record);

			$em->flush();
		}
	}
}