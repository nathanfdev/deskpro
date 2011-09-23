<?php

namespace Application\DevBundle\DataTest\Generator;

use Orb\Util\Strings;
use Orb\Util\Arrays;

class Denormalized extends Basic
{
	/**
	 * Generates any data required before others, such as companies
	 */
	protected function _runGenPreMisc(\DeskPRO\DBAL\Connection $db, \Symfony\Component\Console\Output\Output $output)
	{
		$output->write("<comment>\nGENERATING PRE-MISC\n</comment>\n");

		$num = $this->dataset->getNumCompanies();
		$output->write("Generating $num companies ... ");

		$c = 0;
		while ($c++ < $num) {
			$db->insert('person_company', array(
				'title' => Strings::randomPronounceable(10)
			));
		}
	}

	protected function _genTicketFieldData(\DeskPRO\DBAL\Connection $db, $fieldinfo, $ticket_id)
	{
		switch ($fieldinfo[1]['type']) {
			case 'int':

				$db->update('ticket_search', array(
					'field_' . $fieldinfo[0] => mt_rand($fieldinfo[1]['range'][0], $fieldinfo[1]['range'][1])
				), array('id' => $ticket_id));

				break;

			case 'text':

				$db->update('ticket_search', array(
					'field_' . $fieldinfo[0] => Strings::randomPronounceable(mt_rand(4, 40)),
				), array('id' => $ticket_id));

				break;

			case 'choice':

				for ($i = 0; $i < $fieldinfo[1]['max_choices']; $i++) {
					$child_data = array(
						'field_id' => $fieldinfo[0],
						'ticket_id' => $ticket_id,
						'value_int' => mt_rand($fieldinfo[1]['range'][0], $fieldinfo[1]['range'][1]),
					);

					$db->insert('ticket_search_fieldassoc', $child_data);
				}

				break;
		}
	}
}
