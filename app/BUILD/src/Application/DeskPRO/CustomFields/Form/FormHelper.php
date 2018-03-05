<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\CustomFields\Form;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Doctrine\ORM\EntityManager;
use Orb\Util\Util;
use Symfony\Component\Form\FormFactoryInterface;

class FormHelper
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager        $em
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(EntityManager $em, FormFactoryInterface $formFactory)
    {
        $this->em          = $em;
        $this->formFactory = $formFactory;
    }

    /**
     * @param CustomDefAbstract $field
     * @param array             $formData
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function buildForm(CustomDefAbstract $field, array $formData)
    {
        $handlerClass = $formData['handler_class'];
        $baseType     = Util::getBaseClassname($handlerClass);
        $modelClass   = 'Application\\DeskPRO\\CustomFields\\Form\\Model\\'.$baseType.'Field';
        $typeClass    = 'Application\\DeskPRO\\CustomFields\\Form\\Type\\'.$baseType.'FieldType';

        $editField = new $modelClass($field);
        $formType  = new $typeClass();
        $form      = $this->formFactory->create($formType, $editField);

        return $form;
    }

    /**
     * @param CustomDefAbstract $field
     * @param array             $formData
     *
     * @throws \Exception
     */
    public function saveFormToField(CustomDefAbstract $field, array $formData)
    {
        $formData   = $this->prepareFormData($field, $formData);
        $form       = $this->buildForm($field, $formData);
        $editField  = $form->getData();
        $modelClass = get_class($editField);

        $this->em->getConnection()->beginTransaction();
        try {
            if ($formData['handler_class'] == 'Application\\DeskPRO\\CustomFields\\Handler\\Choice') {
                $editField->choices_structure = $formData['choices_structure'];
                $editField->default_option    = @$formData['default_option'];
            }

            $property = 'calendar';
            if (property_exists($modelClass, $property) && array_key_exists($property, $formData)) {
                $editField->calendar = $formData[$property];
            }

            $form->submit($formData);
            if (!$form->isValid()) {
                // check if the field's alias is valid
                // e.g. to prevent unique key exceptions
                $aliasErrors = $form->get('alias')->getErrors();
                if (count($aliasErrors)) {
                    throw new \RuntimeException($aliasErrors[0]->getMessage());
                }
            }

            $editField->save(); // sic! save anyway (as it was)
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }
    }

    /**
     * @param CustomDefAbstract $field
     * @param array             $formData
     *
     * @throws \DomainException
     *
     * @return array
     */
    private function prepareFormData(CustomDefAbstract $field, array $formData)
    {
        $handlerClass = empty($formData['handler_class']) ? $field['handler_class'] : $formData['handler_class'];
        if (empty($handlerClass)) {
            throw new \DomainException('missing handler_class');
        }

        if (empty($formData['handler_class'])) {
            $formData['handler_class'] = $handlerClass;
        }
        if (!isset($formData['choices_structure'])) {
            $formData['choices_structure'] = [];
        }

        return $formData;
    }
}
