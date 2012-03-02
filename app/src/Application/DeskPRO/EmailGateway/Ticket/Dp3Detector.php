<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EmailGateway\Ticket;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\Ticket;

/**
 * Tries to find old DP3 style codes in the subject and body, and looks them
 * up in the import_datastore table.
 */
class Dp3Detector implements TicketDetectorInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $_found_person = null;


	/**
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	public function findExistingTicket(AbstractReader $reader)
	{
		$this->_found_person = null;

		$subject_text = $reader->getSubject()->getSubject();

		$body_text = array();
		$body_text[] = $reader->getBodyText()->getBody();
		$body_text[] = strip_tags($reader->getBodyHtml()->getBody());
		$body_text = implode(' ', $body_text);

		$from_email = $reader->getFromAddress()->getEmail();

		#------------------------------
		# Match user gateway codes
		#------------------------------

		$ticket = $this->userMatchSubject($subject_text);
		if (!$ticket) {
			$ticket = $this->userMatchBody($body_text);
		}

		if ($ticket) {

			// In DP3 they must already be on the ticket
			$person = App::getOrm()->getRepository('DeskPRO:Person')->findOneByEmail($from_email);
			if ($ticket->findEmailForPerson($person)) {
				$this->_found_person = $person;
			}

			return $ticket;
		}

		#------------------------------
		# Match tech gateway codes
		#------------------------------

		$ticket = $this->techMatchSubject($subject_text);
		if ($ticket) {
			return $ticket;
		}

		return null;
	}


	/**
	 * Subject codes like: [AAAA-0000-AAAA] [ABC123D4]
	 * That is (1) ticket ref and (2) ticket authcode.
	 *
	 * @param string $subject
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	public function userMatchSubject($subject_text)
	{
		#------------------------------
		# Match ref
		#------------------------------

		if (!preg_match('#\[([0-9]{4}-[A-Za-z]{4}-[0-9]{4})\]#', $subject_text, $m)) {
			return null;
		}
		$old_ref = $m[1];

		#------------------------------
		# Match authcode after ref
		#------------------------------

		$pos = strpos($subject_text, $m[0]);

		if (!preg_match('#\[([a-zA-Z0-9]{8})\]#', $subject_text, $m, null, $pos)) {
			return null;
		}
		$old_auth = $m[1];

		#------------------------------
		# Fetch map info
		#------------------------------

		$map_info = App::getDb()->fetchColumn("SELECT data FROM import_datastore WHERE typename = ?", array('dp3_ticketref_' . $old_ref));
		if ($map_info) {
			$map_info = @unserialize($map_info);
		}
		if (!$map_info) {
			return null;
		}

		#------------------------------
		# Check and return ticket
		#------------------------------

		if ($old_auth != $map_info['old_auth']) {
			return null;
		}

		return App::getOrm()->getRepository('DeskPRO:Ticket')->find($map_info['new_id']);
	}


	/**
	 * In the body we have: <=== AAAA-0000-AAAA --- ABC123D4 ===>
	 * Thats (1) The old ticket ref and (2) the old ticket auth
	 *
	 * @param string $body_text
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	public function userMatchBody($body_text)
	{
		if (!preg_match('#<=== ([0-9]{4}-[A-Za-z]{4}-[0-9]{4}) --- ([a-zA-Z0-9]{8}) ===>#', $body_text, $m)) {
			return null;
		}

		$old_ref  = $m[1];
		$old_auth = $m[2];

		#------------------------------
		# Fetch map info
		#------------------------------

		$map_info = App::getDb()->fetchColumn("SELECT data FROM import_datastore WHERE typename = ?", array('dp3_ticketref_' . $old_ref));
		if ($map_info) {
			$map_info = @unserialize($map_info);
		}
		if (!$map_info) {
			return null;
		}

		#------------------------------
		# Check and return ticket
		#------------------------------

		if ($old_auth != $map_info['old_auth']) {
			return null;
		}

		return App::getOrm()->getRepository('DeskPRO:Ticket')->find($map_info['new_id']);
	}


	/**
	 * Tech subjec codes are like: [AAAA-0000-AAAA-8-asd3fda3]
	 * That is (1) the old ticket ref (2) the old tech id (3) substr(md5(old tech pass . old ticket auth), 0, 8)
	 *
	 * @param string $subject_text
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	public function techMatchSubject($subject_text)
	{
		if (!preg_match('#\[([0-9]{4}-[A-Za-z]{4}-[0-9]{4})-([0-9]+)-([a-zA-Z0-9]{8})\]#', $subject_text, $m)) {
			return null;
		}

		$old_ref       = $m[1];
		$old_tech_id   = $m[2];
		$old_tech_auth = $m[3];

		#------------------------------
		# Fetch map info
		#------------------------------

		$map_info = App::getDb()->fetchColumn("SELECT data FROM import_datastore WHERE typename = ?", array('dp3_ticketref_' . $old_ref));
		if ($map_info) {
			$map_info = @unserialize($map_info);
		}
		if (!$map_info) {
			return null;
		}

		$techmap_info = App::getDb()->fetchColumn("SELECT data FROM import_datastore WHERE typename = ?", array('dp3_techpass_' . $old_tech_id));
		if ($techmap_info) {
			$techmap_info = @unserialize($techmap_info);
		}
		if (!$techmap_info) {
			return null;
		}

		#------------------------------
		# Check and return ticket
		#------------------------------

		$check_tech_auth = substr(md5($techmap_info['old_pass'] . $map_info['old_auth']), 0, 8);
		if ($check_tech_auth != $old_tech_auth) {
			return null;
		}

		$agent = App::getOrm()->getRepository('DeskPRO:Person')->find($techmap_info['new_id']);
		if (!$agent) {
			return null;
		}

		$this->_found_person = $agent;
		return App::getOrm()->getRepository('DeskPRO:Ticket')->find($map_info['new_id']);
	}


	/**
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function findExistingPerson(Ticket $ticket, AbstractReader $reader)
	{
		return $this->_found_person;
	}


	/**
	 * @return bool
	 */
	public function canAddUnknownPerson()
	{
		return false;
	}
}
