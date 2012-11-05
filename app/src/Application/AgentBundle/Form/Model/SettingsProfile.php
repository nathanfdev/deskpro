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
 * @subpackage AgentBundle
 */

namespace Application\AgentBundle\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;

class SettingsProfile
{
	public $name;
	public $override_display_name;
	public $email;
	public $timezone = 'UTC';
	public $password = '';
	public $password2 = '';
	public $ticket_signature = '';
	public $new_picture_blob_id = false;
	public $is_html_signature = false;

	public $ticket_close_reply = false;
	public $ticket_close_note = false;

	protected $_blob_inline_ids = array();

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	public function __construct(Person $person)
	{
		$this->em = App::getOrm();

		$this->person = $person;

		$this->name = $person->name;
		$this->override_display_name = $person->override_display_name;
		$this->email = $person->getPrimaryEmailAddress();
		$this->timezone = $person->timezone;

		$this->ticket_close_reply = (bool)$person->getPref('agent.ticket_close_reply', true);
		$this->ticket_close_note = (bool)$person->getPref('agent.ticket_close_note', true);
	}

	public function setBlobInlineIds(array $ids)
	{
		$this->_blob_inline_ids = $ids;
	}

	public function getPerson()
	{
		return $this->person;
	}

	public function requiresAuth()
	{
		if ($this->password || $this->email != $this->person->getPrimaryEmailAddress()) {
			return true;
		}

		return false;
	}

	public function save()
	{
		$this->em->beginTransaction();

		$person = $this->person;

		try {
			$person->name = $this->name;
			$person->override_display_name = $this->override_display_name;
			$person->timezone = $this->timezone;

			if ($this->new_picture_blob_id) {
				$blob = $this->em->getRepository('DeskPRO:Blob')->getByAuthId($this->new_picture_blob_id);
				if ($blob) {
					$person->picture_blob = $blob;
				}
			}

			$primary_email = $person->getPrimaryEmail();
			if ($primary_email->email != $this->email) {

				$found_email = $person->findEmailAddress($this->email);
				if ($found_email) {
					$new_primary_email = $found_email;
				} else {
					$new_primary_email = new \Application\DeskPRO\Entity\PersonEmail();
					$new_primary_email->email = $this->email;
					$new_primary_email->is_validated = true;
					$person->addEmailAddress($new_primary_email);
					$this->em->persist($new_primary_email);
				}

				$person->primary_email = $new_primary_email;

				$person->removeEmailAddressId($primary_email->id);
				$this->em->remove($primary_email);
			}

			if ($this->password) {
				$person->setPassword($this->password);
			}

			if ($this->person->PermissionsManager->GeneralChecker->canSetSignature()) {
				if ($this->is_html_signature && $this->person->PermissionsManager->GeneralChecker->canSetSignatureRte()) {
					$signature_html = App::get('deskpro.core.input_cleaner')->clean($this->ticket_signature, 'html_core');

					foreach ($this->_blob_inline_ids AS $blob_id) {
						$blob = App::getEntityRepository('DeskPRO:Blob')->find($blob_id);
						if ($blob) {
							$regex = '#(<img[^>]+src=")' . preg_quote($blob->getDownloadUrl(true), '#') . '("[^>]*>)#i';
							$replace = $blob->getEmbedCode(true, 'signature_image');
							$signature_html = preg_replace($regex, $replace, $signature_html);
						}
					}

					$regex = '#<img[^>]+class="dp-signature-image" alt="([^"]+)"[^>]*>#i';
					$signature_html = preg_replace($regex, '$1', $signature_html);

					$signature_html = str_replace(array('<div', '</div>'), array('<p', '</p>'), $signature_html);
					$signature_html = preg_replace('/^<p>/', '<p class="dp-signature-start">', trim($signature_html));

					$signature = strip_tags($signature_html);
				} else {
					$signature = $this->ticket_signature;
					$signature_html = nl2br(htmlspecialchars($signature));
				}
				$person->setPreference('agent.ticket_signature', $signature);
				$person->setPreference('agent.ticket_signature_html', $signature_html);
			}

			$person->setPreference('agent.ticket_close_reply', $this->ticket_close_reply ? 1 : 0);
			$person->setPreference('agent.ticket_close_note', $this->ticket_close_note ? 1 : 0);

			$this->em->persist($person);
			$this->em->flush();
			$this->em->commit();

		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}
	}
}
