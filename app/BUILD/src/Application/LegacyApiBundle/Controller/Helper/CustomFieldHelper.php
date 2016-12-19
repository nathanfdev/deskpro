<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
     * @param $form_data
     *
     * @throws \Exception
     */
    public function saveFormToField(CustomDefAbstract $field, array $form_data)
    {
        $basetype    = Util::getBaseClassname($field['handler_class']);
        $model_class = 'Application\\LegacyApiBundle\\Form\\CustomField\\Model\\'.$basetype.'Field';
        $type_class  = 'Application\\LegacyApiBundle\\Form\\CustomField\\Type\\'.$basetype.'FieldType';

        if (!isset($form_data['choices_structure'])) {
            $form_data['choices_structure'] = [];
        }

        $editfield = new $model_class($field);
        $formtype  = new $type_class();
        $form      = $this->controller->getContainer()->get('form.factory')->create($formtype, $editfield);

        $this->em->getConnection()->beginTransaction();
        try {
            if ($field['handler_class'] == 'Application\\DeskPRO\\CustomFields\\Handler\\Choice') {
                $editfield->choices_structure = $form_data['choices_structure'];
                $editfield->default_option    = @$form_data['default_option'];
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
