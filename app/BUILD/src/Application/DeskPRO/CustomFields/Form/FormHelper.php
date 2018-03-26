<?php

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
        $form      = $this->formFactory->create($typeClass, $editField);

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
                $errors = $form->getErrors(true);
                if (count($errors)) {
                    throw new \RuntimeException($errors[0]->getMessage());
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
