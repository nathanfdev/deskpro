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
namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * We have ticket participant property but we need to split it to separate "followers" and "cc" collections.
 * So we need a custom violation mapper to handle such structure.
 */
class TicketParticipantsViolationMapper
{
    /**
     * @var string
     */
    private $followers_field_name;

    /**
     * @var
     */
    private $cc_field_name;

    /**
     * Constructor.
     *
     * @param string $followers_field_name
     * @param string $cc_field_name
     */
    public function __construct($followers_field_name = FormFields::FOLLOWERS, $cc_field_name = FormFields::CC)
    {
        $this->followers_field_name = $followers_field_name;
        $this->cc_field_name        = $cc_field_name;
    }

    /**
     * @param FormEvent $event
     */
    public function __invoke(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $followers_form = $form->get($this->followers_field_name);
        $cc_form        = $form->get($this->cc_field_name);

        $reset_errors = [];

        // move form errors to particular fields
        foreach ($form->getErrors() as $error) {
            $participant = $this->getParticipant($error, $data);
            if (!$participant) {
                continue;
            }

            foreach ([$followers_form, $cc_form] as $participants_form) {
                if (!$participants_form) {
                    continue;
                }

                /** @var FormInterface $participant_form */
                foreach ($participants_form as $participant_form) {
                    if ($participant_form->getData() === $participant) {
                        $participant_form->addError($error);

                        $reset_errors[] = $error;
                    }
                }
            }
        }

        // reset participant errors
        $errors = [];
        foreach ($form->getErrors() as $error) {
            if (!in_array($error, $reset_errors, true)) {
                $errors[] = $error;
            }
        }

        $property = new \ReflectionProperty($form, 'errors');
        $property->setAccessible(true);
        $property->setValue($form, $errors);
        $property->setAccessible(false);
    }

    /**
     * @param FormError $error
     * @param Ticket    $ticket
     *
     * @return int
     */
    protected function getParticipant(FormError $error, Ticket $ticket)
    {
        /** @var ConstraintViolation $violation */
        $violation = $error->getCause();

        if (preg_match('#data.participants\[(\d+)\]#', $violation->getPropertyPath(), $matches)) {
            return $ticket->getParticipants()->get($matches[1]);
        }

        return false;
    }
}
