<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * We apply this listener to all forms in the API so that an API user is not required to
 * pass optional vales during a POST operation. For example, the Filter entity will set
 * "display_order" to 0 on construction and it is not a required value on the form.
 *
 * Therefore, we need to act as if the user submitted the default data, otherwise the form
 * will freak out during a POST operation because all fields are expected to be submitted
 * ("required" on a form only means null is an allowed value, but it must be in the submitted
 * data array)
 */
class ReplaceNotSubmittedValuesWithDefaultsListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        // only listen to compound forms
        if ($form->getConfig()->getCompound()) {
            foreach ($form->all() as $name => $child_form) {
                // if this form field was not submitted
                if (!array_key_exists($name, $data)) {
                    // and if this form field is not required
                    if (!$child_form->isRequired()) {
                        // then add its default data to the submitted data for processing
                        $data[$name] = $child_form->getData();
                    }
                }
            }
        }

        $event->setData($data);
    }
}
