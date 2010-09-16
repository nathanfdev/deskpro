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
		// TODO mappers
	}
}