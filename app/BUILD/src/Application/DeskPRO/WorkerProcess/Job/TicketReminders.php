<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use DeskPRO\Bundle\PortalBundle\Helper\PortalValidation;
use DeskPRO\Bundle\PortalBundle\Model\EmailTo;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Executes escalations.
 */
class TicketReminders extends AbstractJob
{
    const DEFAULT_INTERVAL = 60;

    /** @var int */
    protected $count_reminders_sent;

    public function run()
    {
        /** @var \DeskPRO\Bundle\AppBundle\Entity\Repository\SavedFormRepository $saved_form_repo */
        $saved_form_repo = App::$container->getEm()->getRepository(SavedForm::class);

        $first_reminders  = $saved_form_repo->getForTicketReminders(0, new \DateTime('now - 3 hours'));
        $second_reminders = $saved_form_repo->getForTicketReminders(1, new \DateTime('now - 24 hours'));
        $third_reminders  = $saved_form_repo->getForTicketReminders(2, new \DateTime('now - 48 hours'));

        $this->sendReminders($first_reminders);
        $this->sendReminders($second_reminders);
        $this->sendReminders($third_reminders);
    }

    /**
     * @param SavedForm[] $saved_forms
     */
    protected function sendReminders($saved_forms)
    {
        foreach ($saved_forms as $saved_form) {
            $this->sendReminder($saved_form);
        }
    }

    protected function sendReminder(SavedForm $saved_form)
    {
        try {
            $this->sendTicketReminder($saved_form);
            $saved_form->incrementSentReminders();
            App::$container->getEm()->flush($saved_form);
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e, false);
        }
    }

    protected function sendTicketReminder(SavedForm $saved_form)
    {
        $validate_url = App::$container->getRouter()->generate('portal_validation',
            ['type' => PortalValidation::NEW_TICKET, 'auth_code' => $saved_form->getAuthCode()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        if ($person = $saved_form->getPerson()) {
            $email_to = new EmailTo($person);
        } else {
            if (!$email = $saved_form->getMetaDataValue('email')) {
                throw new \InvalidArgumentException(
                    'trying to send a verification email, but no email provided. saved form must have a Person, or its metadata must have an "email" key.'
                );
            }
            $name     = $saved_form->getMetaDataValue('name');
            $email_to = new EmailTo();
            $email_to->setTo($email, $name);
        }

        if (App::$container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = App::$container->get('email.user_viewmodel_factory')
                ->createTicketNewReminderModel($validate_url, $saved_form->getDateExpires());
            App::$container->get('email.email_sender')
                ->send($viewModel, ['to' => $email_to]);
        } else {
            $message = App::getMailer()->createMessage();
            if ($person = $email_to->getPerson()) {
                $message->setToPerson($person);
            } else {
                $message->setTo($email_to->getEmailAddress(), $email_to->getName());
            }
            $message->setTemplate('DeskPRO:emails_user:ticket-new-reminder.html.twig', [
                'verify_url'  => $validate_url,
                'expire_date' => $saved_form->getDateExpires(),
            ]);

            App::getMailer()->send($message);
        }
    }
}
