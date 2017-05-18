<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\LegacyApiBundle\Controller\Helper;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\LegacyApiBundle\Controller\AbstractController;
use Orb\Util\Util;

class CustomFieldHelper
{
    /**
     * @var \Application\LegacyApiBundle\Controller\AbstractController
     */
    private $controller;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(AbstractController $controller)
    {
        $this->controller = $controller;
        $this->em         = $controller->getContainer()->getEm();
    }

    /**
     * @param CustomDefAbstract $field
     * @param $formData
     *
     * @throws \Exception
     */
    public function saveFormToField(CustomDefAbstract $field, array $formData)
    {
        $baseType   = Util::getBaseClassname($field['handler_class']);
        $modelClass = 'Application\\LegacyApiBundle\\Form\\CustomField\\Model\\'.$baseType.'Field';
        $typeClass  = 'Application\\LegacyApiBundle\\Form\\CustomField\\Type\\'.$baseType.'FieldType';

        if (!isset($formData['choices_structure'])) {
            $formData['choices_structure'] = [];
        }

        $editField = new $modelClass($field);
        $formType  = new $typeClass();
        $form      = $this->controller->getContainer()->get('form.factory')->create($formType, $editField);

        $this->em->getConnection()->beginTransaction();
        try {
            if ($field['handler_class'] == 'Application\\DeskPRO\\CustomFields\\Handler\\Choice') {
                $editField->choices_structure = $formData['choices_structure'];
                $editField->default_option    = @$formData['default_option'];
            }

            // todo
            if (empty($formData['handler_class'])) {
                $formData['handler_class'] = $editField->handler_class ?: $field['handler_class'];
            }
            $property = 'calendar';
            if (property_exists($modelClass, $property) && array_key_exists($property, $formData)) {
                $editField->calendar = $formData[$property];
            }

            $form->submit($formData);
            $editField->save();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }
    }
}
