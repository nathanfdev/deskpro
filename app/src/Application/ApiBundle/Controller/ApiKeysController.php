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
 */

namespace Application\ApiBundle\Controller;

use Orb\Util\Arrays;

use Application\DeskPRO\Exception\ValidationException;

use Application\DeskPRO\ApiKeys\ApiKeys;
use Application\DeskPRO\ApiKeys\ApiKeyEdit;
use Application\DeskPRO\ApiKeys\Form\Type\ApiKeyType;

class ApiKeysController extends AbstractController
{
	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction()
	{
		/**
		 * @var \Application\DeskPRO\ApiKeys\ApiKeys $api_keys
		 */

		$api_keys = $this->container->getSystemService('api_keys');

		return $this->createApiResponse(
			array(
				 'api_keys' => $api_keys->getAllWithUserAsArray()
			)
		);
	}

	###################################################################################################################
	# get
	####################################################################################################################

	public function getAction($id)
	{
		/**
		 * @var \Application\DeskPRO\ApiKeys\ApiKeys $api_keys
		 */

		$api_keys = $this->container->getSystemService('api_keys');
		$api_key  = $api_keys->getWithUserById($id);

		if (!$api_key) {

			throw $this->createNotFoundException();
		}

		$returnedData               = $api_key;
		$returnedData['all_agents'] = $api_keys->getAllAgents();

		return $this->createApiResponse(
			array(
				 'api_key' => $returnedData
			)
		);
	}

	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction($id)
	{
		/**
		 * @var \Application\DeskPRO\ApiKeys\ApiKeys $api_keys
		 */

		$api_keys = $this->container->getSystemService('api_keys');

		if ($id) {

			$api_key = $api_keys->getById($id);

			if (!$api_key) {

				throw $this->createNotFoundException();
			}
		} else {

			$api_key = $api_keys->createNew();
		}

		$postData = $this->in->getAll('post');

		$api_key_edit = new ApiKeyEdit($api_key);

		$form = $this->createForm(new ApiKeyType(), $api_key_edit, array('cascade_validation' => true));
		$form->submit($this->deleteExtraDataFromRequest($form, $postData, 'api_key'), true);

		if ($form->isValid()) {

			$api_key_edit->save($this->em);

		} else {

			throw ValidationException::create($this->getFormValidationErrorsString($form));
		}

		return $this->createApiResponse(
			array(
				 'success' => true,
				 'id'      => $api_key->id,
			)
		);
	}

	####################################################################################################################
	# remove
	####################################################################################################################

	public function removeAction($id)
	{
		/**
		 * @var \Application\DeskPRO\ApiKeys\ApiKeys $api_keys
		 */

		$api_keys = $this->container->getSystemService('api_keys');
		$api_key  = $api_keys->getById($id);

		if (!$api_key) {

			throw $this->createNotFoundException();
		}

		$old_id = $api_key->id;

		$this->db->beginTransaction();

		try {

			$this->em->remove($api_key);
			$this->em->flush();

			$this->db->commit();

		} catch(\Exception $e) {

			$this->db->rollback();
			throw $e;
		}

		return $this->createSuccessResponse(array('old_id' => $old_id));
	}


	####################################################################################################################
	# regenerate
	####################################################################################################################

	public function regenerateAction($id)
	{
		/**
		 * @var \Application\DeskPRO\ApiKeys\ApiKeys $api_keys
		 */

		$api_keys = $this->container->getSystemService('api_keys');
		$api_key  = $api_keys->getById($id);

		if (!$api_key) {

			throw $this->createNotFoundException();
		}

		$api_key->regenerateApiKey();

		$this->em->persist($api_key);
		$this->em->flush();

		return $this->createSuccessResponse(array('code' => $api_key->code, 'keyString' => $api_key->keyString));
	}
}