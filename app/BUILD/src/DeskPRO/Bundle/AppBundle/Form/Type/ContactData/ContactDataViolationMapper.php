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
 */
namespace DeskPRO\Bundle\AppBundle\Form\Type\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * We store contact data in a single collection but the form has grouped collections.
 * So we should map errors to their form groups.
 */
class ContactDataViolationMapper
{
    /**
     * @var string
     */
    private $form_name;

    /**
     * Constructor.
     *
     * @param string $form_name
     */
    public function __construct($form_name)
    {
        $this->form_name = $form_name;
    }

    /**
     * @param FormEvent $event
     */
    public function __invoke(FormEvent $event)
    {
        $parent_form = $event->getForm();

        $form = $parent_form->get($this->form_name);
        $data = $parent_form->getData()->getContactData();

        // set children errors
        foreach ($form->getErrors() as $error) {
            $property_path = $this->parsePropertyPath($error->getCause());

            if (is_array($property_path)) {
                list($index, $field) = $property_path;

                /** @var ContactDataAbstract $entity */
                $entity = $data[$index];
                $type   = $entity->getContactType();

                $type_form = $form->get($type);
                $index     = $this->correctIndex($entity, $type_form);

                if ($index !== false) {
                    $entity_form = $type_form->get($index);
                    foreach ($entity_form->all() as $child_form) {
                        $child_property_path = $child_form->getConfig()->getOption('property_path');
                        if ($child_property_path === $field) {
                            $field = $child_form->getName();
                        }
                    }

                    $error_mapping = $entity_form->getConfig()->getOption('error_mapping');
                    if (isset($error_mapping[$field])) {
                        $field = $error_mapping[$field];
                    }

                    $entity_form->get($field)->addError($error);
                }
            }
        }

        // reset contact data errors
        $property = new \ReflectionProperty($form, 'errors');
        $property->setAccessible(true);
        $property->setValue($form, []);
        $property->setAccessible(false);
    }

    /**
     * @param ConstraintViolation $violation
     *
     * @return array|false
     */
    protected function parsePropertyPath(ConstraintViolation $violation)
    {
        $property_path = $violation->getPropertyPath();
        if (preg_match('#data\.'.$this->form_name.'\[(\d+)\]\.([\w\d_]+)#', $property_path, $matches)) {
            return [
                $matches[1],
                $matches[2],
            ];
        }

        return false;
    }

    /**
     * @param ContactDataAbstract $entity
     * @param FormInterface       $type_form
     *
     * @return int|false
     */
    protected function correctIndex(ContactDataAbstract $entity, FormInterface $type_form)
    {
        foreach ($type_form->all() as $child_form) {
            if ($entity === $child_form->getData()) {
                return (int) $child_form->getName();
            }
        }

        return false;
    }
}
