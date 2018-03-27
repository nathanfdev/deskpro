<?php

namespace DeskPRO\Bundle\AppBundle\Form\Error;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Class FormValidatorChecker.
 */
class FormValidatorChecker
{
    /**
     * @param FormInterface $form
     */
    public static function submitForm(FormInterface $form)
    {
        // "submit" all form fields to proper validation mapping
        $submitIterator = function (FormInterface $form) use (&$submitIterator) {
            $form->submit(null);
            foreach ($form->all() as $child) {
                $submitIterator($child);
            }
        };

        $submitIterator($form);

        // trigger form validation
        $validationIterator = function (FormInterface $form) use (&$validationIterator) {
            $dispatcher = $form->getConfig()->getEventDispatcher();
            $dispatcher->dispatch(FormEvents::POST_SUBMIT, new FormEvent($form, null));

            foreach ($form->all() as $child) {
                $validationIterator($child);
            }
        };

        $validationIterator($form);
    }

    /**
     * @param FormInterface $form
     * @param bool          $clearChildren
     */
    public static function clearFormErrors(FormInterface $form, $clearChildren = true)
    {
        if (!$form instanceof Form) {
            return;
        }

        $property = new \ReflectionProperty(Form::class, 'errors');
        $property->setAccessible(true);
        $property->setValue($form, []);
        $property->setAccessible(false);

        if ($clearChildren) {
            foreach ($form->all() as $child) {
                self::clearFormErrors($child);
            }
        }
    }
}
