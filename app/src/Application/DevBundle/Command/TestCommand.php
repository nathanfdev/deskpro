<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Commands
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DevBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;

use Orb\Util\Arrays;
use Orb\Util\Strings;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Routing\Route;

class TestCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dpdev:test');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$date = new \DateTime("-6 months");
		$now = new \DateTime();

		while ($date->add(new \DateInterval('P1D')) < $now) {
			for ($i = 0; $i < 20; $i++) {
				$info = App::getDb()->fetchAssoc("
					SELECT id, person_id, agent_id
					FROM tickets
					WHERE agent_id IS NOT NULL
					ORDER BY RAND()
					LIMIT 1
				");
				$message_id = App::getDb()->fetchColumn("
					SELECT id
					FROM tickets_messages
					WHERE ticket_id = ? AND person_id = ?
					ORDER BY id DESC
					LIMIT 1
				", array($info['id'], $info['agent_id']));

				if (!$message_id) {
					continue;
				}

				if (mt_rand(1,10) < 6) {
					$rating = 1;
				} else {
					$rating = -1;
				}

				App::getDb()->insert('ticket_feedback', array(
					'ticket_id' => $info['id'],
					'message_id' => $message_id,
					'person_id' => $info['person_id'],
					'rating' => $rating,
					'message' => 'Test Message',
					'date_created' => $date->format('Y-m-d H:i:s')
				));
			}
		}

		echo "\n";
	}
}
