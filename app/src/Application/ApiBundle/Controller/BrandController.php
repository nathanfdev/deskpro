<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Brand;

class BrandController extends AbstractController
{
	public function listAction()
	{
		$brands = $this->getBrandRepo()->findAll();

		$serialized_brands = $this->container->getSerializer()->serializeArray($brands);

		return $this->createApiResponse(array('brands' => $serialized_brands));
	}

	public function showAction($id)
	{
		$brand = $this->getBrandRepo()->find($id);

		if (!$brand) {
			throw $this->createNotFoundException('could not find id="'.$id.'"');
		}

		$serialized_brand = $this->container->getSerializer()->serialize($brand);

		return $this->createApiResponse($serialized_brand);
	}

	public function saveAction($id = 0)
	{
		if ($id) {
			$brand = $this->getBrandRepo()->find($id);
			$http_status = 200;
		} else {
			$brand = new Brand();
			$http_status = 201;
		}

		$errors = array();

		$name = $this->in->getString('brand.name');
		if (!$name) {
			$errors[] = 'Your brand must have a name';
		}

		if ($this->in->getBool('unset_logo')) {
			if ($brand->logo_blob) {
				$old_blob             = $brand->logo_blob;
				$brand->logo_blob = null;

				try {
					$this->container->getBlobStorage()->deleteBlobRecord($old_blob);
				} catch (\Exception $e) {
				}
			}
		} elseif ($blobAuthCode = $this->in->getString('set_logo_blob')) {
			if ($brand->logo_blob) {
				$old_blob             = $brand->logo_blob;
				$brand->logo_blob = null;

				try {
					$this->container->getBlobStorage()->deleteBlobRecord($old_blob);
				} catch (\Exception $e) {
				}
			}

			$blob = $this->em->getRepository('DeskPRO:Blob')->getByAuthCode($blobAuthCode);
			if ($blob && $blob->isImage()) {
				$brand->logo_blob = $blob;
			}
		}

		if (!count($errors)) {
			$brand->name = $name;

			$this->container->getEm()->persist($brand);
			$this->container->getEm()->flush();

			$serialized_brand = $this->container->getSerializer()->serialize($brand);

			return $this->createApiSuccessResponse($serialized_brand, $http_status);
		}

		return $this->createApiMultipleErrorResponse($errors);
	}

	public function removeAction($id)
	{
		$brand = null;
		if ($id) {
			$brand = $this->getBrandRepo()->find($id);
		}

		if (!$brand) {
			throw $this->createNotFoundException('brand not found for id = "' . $id . '"');
		}

		$this->container->getEm()->remove($brand);
		$this->container->getEm()->flush();

		return $this->createApiSuccessResponse();
	}


	/**
	 * @return \Application\DeskPRO\EntityRepository\Brand
	 */
	protected function getBrandRepo()
	{
		return $this->container->getEm()->getRepository('DeskPRO:Brand');
	}
}