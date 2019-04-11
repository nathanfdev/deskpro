<?php

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
    private $formName;

    /**
     * Constructor.
     *
     * @param string $formName
     */
    public function __construct($formName)
    {
        $this->formName = $formName;
    }

    /**
     * @param FormEvent $event
     */
    public function __invoke(FormEvent $event)
    {
        $parentForm = $event->getForm();

        $form = $parentForm->get($this->formName);
        $data = $parentForm->getData()->getContactData();

        // set children errors
        foreach ($form->getErrors() as $error) {
            $propertyPath = $this->parsePropertyPath($error->getCause());

            if (is_array($propertyPath)) {
                list($index, $field) = $propertyPath;

                /** @var ContactDataAbstract $entity */
                $entity = $data[$index];
                $type   = $entity->getContactType();

                $typeForm = $form->get($type);
                $index    = $this->correctIndex($entity, $typeForm);

                if ($index !== false && $typeForm->has($index)) {
                    $entityForm = $typeForm->get($index);
                    foreach ($entityForm->all() as $childForm) {
                        $childPropertyPath = $childForm->getConfig()->getOption('property_path');
                        if ($childPropertyPath === $field) {
                            $field = $childForm->getName();
                        }
                    }

                    $errorMapping = $entityForm->getConfig()->getOption('error_mapping');
                    if (isset($errorMapping[$field])) {
                        $field = $errorMapping[$field];
                    }

                    $entityForm->get($field)->addError($error);
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
        $propertyPath = $violation->getPropertyPath();
        if (preg_match('#data\.'.$this->formName.'\[(\d+)\]\.([\w\d_]+)#', $propertyPath, $matches)) {
            return [
                $matches[1],
                $matches[2],
            ];
        }

        return false;
    }

    /**
     * @param ContactDataAbstract $entity
     * @param FormInterface       $typeForm
     *
     * @return int|false
     */
    protected function correctIndex(ContactDataAbstract $entity, FormInterface $typeForm)
    {
        foreach ($typeForm->all() as $child_form) {
            if ($entity === $child_form->getData()) {
                return (int) $child_form->getName();
            }
        }

        return false;
    }
}
