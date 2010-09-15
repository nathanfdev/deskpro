<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\RemoteResourceListener;

use \Application\CoreBundle\Entity\RemoteResource;
use \Application\CoreBundle\Entity\RemoteRecord;

/**
 * When any Auth-related RemoteResource is updated, we need to process the associated
 * user as well.
 *
 * This will INITIALIZE the user if they don't already exist!
 */
class Auth extends AbstractListener
{
	public function remoteRecordUpdated(RemoteResource $resource, RemoteRecord $record)
	{
		// 1: Find the PersonData field mapped to this $record
		try {
			$person_data = $this->em->getRepository('CoreBundle:PersonData')->findOneBy(array(
				'remote_record_id' => $record['id']
			));

			$person = $person_data['person'];
		} catch (\Doctrine\ORM\NoResultException $e) {
			$person = null;
		}

		if (!$person) {
			$person = $this->initializePerson($record);
		}
	}
}