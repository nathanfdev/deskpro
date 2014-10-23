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
 * @category Entities
 */

namespace Application\ApiBundle\Controller\Helper;

use Application\ApiBundle\Controller\AbstractController;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Orb\Util\Util;

class CustomFieldHelper
{
	/**
	 * @var \Application\ApiBundle\Controller\AbstractController
	 */
	private $controller;

	/**
 	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Application\DeskPRO\Input\Reader
	 */
	private $in;

	public function __construct(AbstractController $controller)
	{
		$this->controller = $controller;
		$this->em = $controller->getContainer()->getEm();
		$this->in = $controller->getContainer()->getIn();
	}

	/**
	 * @param CustomDefAbstract $field
	 * @param $form_data
	 * @throws \Exception
	 */
	public function saveFormToField(CustomDefAbstract $field, array $form_data)
	{
		$basetype    = Util::getBaseClassname($field['handler_class']);
		$model_class = 'Application\\ApiBundle\\Form\\CustomField\\Model\\' . $basetype . 'Field';
		$type_class  = 'Application\\ApiBundle\\Form\\CustomField\\Type\\' . $basetype . 'FieldType';

		$editfield = new $model_class($field);
		$formtype  = new $type_class();
		$form      = $this->controller->getContainer()->get('form.factory')->create($formtype, $editfield);

		$this->em->getConnection()->beginTransaction();
		try {
			if ($field['handler_class'] == 'Application\\DeskPRO\\CustomFields\\Handler\\Choice') {
				$editfield->choices_structure = $this->in->getArrayValue('choices_structure');
				$editfield->default_option = $this->in->getString('default_option');
			}

			// todo
			if (empty($form_data['handler_class'])) {
				$form_data['handler_class'] = $editfield->handler_class ?: $field['handler_class'];
			}
			$form->submit($form_data);
			$editfield->save();

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}
	}
}