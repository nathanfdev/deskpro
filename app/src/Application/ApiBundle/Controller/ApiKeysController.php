<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\DeskPRO\Form\Type\ApiKeyType;
use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
* @SWG\Resource(
* 	resourcePath="/api_keys",
* 	description="Operations about API Keys",
* 	basePath="/api/api_keys"
* )
*/

class ApiKeysController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new AdminManagePermission();
	}


	###################################################################################################################
	# list
	####################################################################################################################

	/**
	 * @SWG\Api(
	 * 	path="/api_keys",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Get list of all existing API Keys",
	 * 		notes="Returns array of all existing API Keys"
	 * 	)
	 * )
	 */

	public function listAction()
	{
		$keys = $this->em->getRepository('DeskPRO:ApiKey')->findAll();
		return $this->createApiResponse($this->getApiData($keys, false) ?: array());
	}

	###################################################################################################################
	# get
	####################################################################################################################

	/**
	 * @SWG\Api(
	 * 	path="/api_keys/{id}",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Find API Key By ID",
	 * 		notes="Returns API Key based on ID",
	 * 		@SWG\Parameter(
	 * 			name="id",
	 * 			description="ID of API Key that needs to be fetched",
	 * 			required=true,
	 * 			type="integer",
	 * 			paramType="path"
	 * 		),
	 * 		@SWG\ResponseMessage(code=404, message="API Key not found")
	 * 	)
	 * )
	 */

	public function getAction($id)
	{
		if (!$key = $this->em->find('DeskPRO:ApiKey', $id)) {
			throw $this->createNotFoundException();
		}

		return $this->createApiResponse($this->getApiData($key));
	}

	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction(Request $request, $id)
	{
		/** @var $key ApiKey */
		if ($id) {
			if (!$key = $this->em->find('DeskPRO:ApiKey', $id)) {
				throw $this->createNotFoundException();
			}
		} else {
			$key = new ApiKey();
			$this->em->persist($key);
		}

		$data = $this->in->getAll('req');
		$form = $this->createForm(new ApiKeyType(), $key);
		$form->submit($data);

		if ($form->isValid()) {
			$this->em->flush($key);
		} else {
			return $this->createApiErrorInfoResponse('validation_rrror', $this->getFormValidationErrorsString($form), array());
		}

		return $this->getAction($key['id']);
	}

	####################################################################################################################
	# remove
	####################################################################################################################

	public function removeAction($id)
	{
		/** @var $key ApiKey */
		if (!$key = $this->em->find('DeskPRO:ApiKey', $id)) {
			throw $this->createNotFoundException();
		}

		$old_id = $key['id'];

		$this->em->remove($key);
		$this->em->flush();

		return $this->createSuccessResponse(array('old_id' => $old_id));
	}


	####################################################################################################################
	# regenerate
	####################################################################################################################

	public function regenerateAction($id)
	{
		/** @var $key ApiKey */
		if (!$key = $this->em->find('DeskPRO:ApiKey', $id)) {
			throw $this->createNotFoundException();
		}

		$key->regenerateApiKey();
		$this->em->flush();

		return $this->createSuccessResponse(array('code' => $key['code'], 'keyString' => $key['keyString']));
	}

    /**
     * @param $logEntryId
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function replayLogEntryAction($logEntryId)
    {
        /** @var $entry \Application\DeskPRO\Entity\ApiKeyLog */
        if (!$entry = $this->em->find('DeskPRO:ApiKeyLog', $logEntryId)) {
            throw new NotFoundHttpException;
        }
	    /** @var ApiKey $key */
	    $key = $entry->key;
	    $request = $entry['request'];

	    $api = new \DeskPRO\Api($this->settings->get('core.deskpro_url'), $key->getKeyString(), $key->person['id']);
	    $path = 0 === strpos($request['path'], '/api') ? substr($request['path'], 4) : $request['path'];
	    /** @var \DeskPRO\Api\Result $response */
	    $response = $api->call($request['method'], $path, $request['payload']);

	    $result = array(
		    'status' => $response->getResponseCode(),
		    'content' => $response->getData(),
	    );

        return $this->createApiResponse($result);
    }
}