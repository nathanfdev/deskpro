<?php

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Brand;
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

    /**
     * @var int
     */
    protected $countRemindersSent;

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        /** @var \DeskPRO\Bundle\AppBundle\Entity\Repository\SavedFormRepository $savedFormRepo */
        $savedFormRepo = App::$container->getEm()->getRepository(SavedForm::class);

        $firstReminders  = $savedFormRepo->getForTicketReminders(0, new \DateTime('now - 3 hours'));
        $secondReminders = $savedFormRepo->getForTicketReminders(1, new \DateTime('now - 24 hours'));
        $thirdReminders  = $savedFormRepo->getForTicketReminders(2, new \DateTime('now - 48 hours'));

        $this->sendReminders($firstReminders);
        $this->sendReminders($secondReminders);
        $this->sendReminders($thirdReminders);
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

    /**
     * @param SavedForm $savedForm
     */
    protected function sendReminder(SavedForm $savedForm)
    {
        try {
            $this->sendTicketReminder($savedForm);
            $savedForm->incrementSentReminders();
            App::$container->getEm()->flush();
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e, false);
        }
    }

    /**
     * @param SavedForm $savedForm
     *
     * @throws \Exception
     */
    protected function sendTicketReminder(SavedForm $savedForm)
    {
        $validateUrlParams = [
            'type'      => PortalValidation::NEW_TICKET,
            'auth_code' => $savedForm->getAuthCode(),
        ];

        if ($brandId = $savedForm->getMetaDataValue('brand')) {
            $brand = $this->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository(Brand::class)->find($brandId);
        } else {
            $brand = null;
        }

        $brandStack = $this->getContainer()->get('brand_stack');
        if ($brand) {
            $brandStack->push($brand);
        }

        try {
            $validateUrl = App::$container->getRouter()->generate('portal_validation',
                $validateUrlParams,
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            if ($person = $savedForm->getPerson()) {
                $emailTo = new EmailTo($person);
            } else {
                if (!$email = $savedForm->getMetaDataValue('email')) {
                    throw new \InvalidArgumentException(
                        'trying to send a verification email, but no email provided. saved form must have a Person, or its metadata must have an "email" key.'
                    );
                }
                $name    = $savedForm->getMetaDataValue('name');
                $emailTo = new EmailTo();
                $emailTo->setTo($email, $name);
            }

            if (App::$container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                $viewModel = App::$container->get('email.user_viewmodel_factory')->createTicketNewReminderModel($validateUrl, $savedForm->getDateExpires());
                App::$container->get('email.email_sender')->send($viewModel, ['to' => $emailTo]);
            } else {
                $message = App::getMailer()->createMessage();
                if ($person = $emailTo->getPerson()) {
                    $message->setToPerson($person);
                } else {
                    $message->setTo($emailTo->getEmailAddress(), $emailTo->getName());
                }
                $message->setTemplate('DeskPRO:emails_user:ticket-new-reminder.html.twig', [
                    'verify_url'  => $validateUrl,
                    'expire_date' => $savedForm->getDateExpires(),
                ]);

                App::getMailer()->send($message);
            }
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e, false);
        } catch (\Throwable $e) {
        }

        if ($brand) {
            $brandStack->pop();
        }
    }
}
