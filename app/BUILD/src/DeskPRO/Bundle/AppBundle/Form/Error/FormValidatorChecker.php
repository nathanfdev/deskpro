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
     */
    public static function clearFormErrors(FormInterface $form)
    {
        if (!$form instanceof Form) {
            return;
        }

        $property = new \ReflectionProperty(Form::class, 'errors');
        $property->setAccessible(true);
        $property->setValue($form, []);
        $property->setAccessible(false);

        foreach ($form->all() as $child) {
            self::clearFormErrors($child);
        }
    }
}
