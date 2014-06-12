<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1402588458 extends AbstractBuild
{
	public function run()
	{
		$this->out("Correcting CheckUserValidEmail and CheckUserValidAgent operators");
		$this->correctTrigger('default_newticket_validemail', 'CheckUserValidEmail');
		$this->correctTrigger('default_newticket_validagent', 'CheckUserValidAgent');
	}

	private function correctTrigger($name, $term_type)
	{
		$trigger = $this->container->getDb()->fetchAssoc("
			SELECT id, terms
			FROM ticket_triggers
			WHERE sys_name = ?
			LIMIT 1
		", array($name));

		$new_terms = $this->procTriggerTerms($trigger['terms'], $term_type, 'not');

		$this->container->getDb()->update('ticket_triggers', array('terms' => $new_terms), array('id' => $trigger['id']));
	}

	/**
	 * @param string $terms      JSON encoded string
	 * @param string $term_type  Term type we're looking for
	 * @param string $new_op     The new op to set
	 * @return string A new JSON string to save
	 */
	private function procTriggerTerms($terms, $term_type, $new_op)
	{
		$terms = json_decode($terms, true);
		$terms['@DATA']['terms'] = $this->procTermsSet($terms['@DATA']['terms'], $term_type, $new_op);

		return json_encode($terms);
	}

	/**
	 * @param array $set
	 * @param string $term_type
	 * @param string $new_op
	 * @return array
	 */
	private function procTermsSet(array $set, $term_type, $new_op)
	{
		foreach ($set as &$t) {
			if (isset($t['set_terms'])) {
				$t['set_terms'] = $this->procTermsSet($t['set_terms'], $term_type, $new_op);
			} else {
				if ($t['type'] == $term_type) {
					$t['op'] = $new_op;
				}
			}
		}
		unset($t);

		return $set;
	}
}