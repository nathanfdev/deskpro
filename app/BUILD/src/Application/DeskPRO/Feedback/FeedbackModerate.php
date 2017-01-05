<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\DeskPRO\Feedback;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Translate\Translate;
use Application\EmailBundle\SwiftMailer\Mailer;
use DeskPRO\Bundle\AppBundle\DataService\Feedback\FeedbackDataService;
use DeskPRO\Bundle\PortalBundle\Brand\BrandContainer;
use DeskPRO\Bundle\SendmailBundle\Factory\UserViewModelFactory;
use DeskPRO\Bundle\SendmailBundle\Sender\EmailSender;
use Doctrine\ORM\EntityManager;
use DpSys\Features;

class FeedbackModerate implements PersonContextInterface
{
    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Translate
     */
    protected $translator;

    /**
     * @var Person
     */
    protected $personContext;

    /**
     * @var FeedbackDataService
     */
    protected $feedbackDataService;

    /**
     * @var BrandContainer
     */
    protected $brandContainer;

    /**
     * @var Features
     */
    protected $featureFlags;

    /**
     * @var UserViewModelFactory
     */
    protected $userViewmodelFactory;

    /**
     * @var EmailSender
     */
    protected $emailSender;

    /**
     * @param DeskproContainer $container
     * @param Person           $person
     */
    public function __construct(DeskproContainer $container, Person $person)
    {
        $this->mailer               = $container->getMailer();
        $this->em                   = $container->getEm();
        $this->translator           = $container->getTranslator();
        $this->feedbackDataService  = $container->get('data.feedback');
        $this->featureFlags         = $container->get('deskpro.feature_flags');
        $this->userViewmodelFactory = $container->get('email.user_viewmodel_factory');
        $this->emailSender          = $container->get('email.email_sender');

        $this->setPersonContext($person);
    }

    /**
     * @param \Application\DeskPRO\Entity\Person $person
     */
    public function setPersonContext(Person $person)
    {
        $this->personContext = $person;
    }

    /**
     * @param \Application\DeskPRO\Entity\Feedback $feedback
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function approveFeedback(Feedback $feedback)
    {
        $becameReviewed = false;
        if ($feedback->getStatus() === Feedback::STATUS_HIDDEN) {
            $statusCategory = $this->feedbackDataService->getFeedbackFirstStatusCategoryByType();
            $feedback
                ->setStatus(Feedback::STATUS_ACTIVE)
                ->setStatusCategory($statusCategory);
        } else {
            $becameReviewed = true;
            $feedback->setIsReviewed(true);
        }

        $this->em->getConnection()->beginTransaction();
        try {
            $this->em->persist($feedback);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }

        if ($becameReviewed) {
            $agent            = $this->personContext;
            $mailer           = $this->mailer;
            $featureFlags     = $this->featureFlags;
            $viewModelFactory = $this->userViewmodelFactory;
            $sender           = $this->emailSender;

            $this->translator->setTemporaryLanguage(
                $feedback->getPerson()->getLanguage(),
                function () use ($mailer, $feedback, $agent, $featureFlags, $viewModelFactory, $sender) {
                    $vars = [
                        'feedback' => $feedback,
                        'agent'    => $agent,
                    ];

                    if ($featureFlags->hasFeature('new_email_templates')) {
                        $viewModel = $viewModelFactory->createFeedbackApprovedModel($feedback, $agent);
                        $sender->send($viewModel, ['to' => $feedback->getPerson()]);
                    } else {
                        $message = $mailer->createMessage();
                        $message->setToPerson($feedback->getPerson());
                        $message->setTemplate('DeskPRO:emails_user:feedback-approved.html.twig', $vars);

                        $mailer->send($message);
                    }
                }
            );
        }
    }

    /**
     * @param Feedback $feedback
     * @param string   $reason
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function disapproveFeedback(Feedback $feedback, $reason = '')
    {
        if (!$reason) {
            $reason = null;
        }

        $this->em->getConnection()->beginTransaction();
        try {
            $this->em->remove($feedback);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }

        $agent            = $this->personContext;
        $mailer           = $this->mailer;
        $featureFlags     = $this->featureFlags;
        $viewModelFactory = $this->userViewmodelFactory;
        $sender           = $this->emailSender;

        $this->translator->setTemporaryLanguage(
            $feedback->getPerson()->getLanguage(),
            function () use ($mailer, $feedback, $agent, $reason, $featureFlags, $viewModelFactory, $sender) {
                $vars = [
                    'feedback' => $feedback,
                    'agent'    => $agent,
                    'reason'   => $reason,
                ];

                if ($featureFlags->hasFeature('new_email_templates')) {
                    $viewModel = $viewModelFactory->createFeedbackDisapprovedModel($feedback, $agent);
                    $sender->send($viewModel, ['to' => $feedback->getPerson()]);
                } else {
                    $message = $mailer->createMessage();
                    $message->setToPerson($feedback->getPerson());
                    $message->setTemplate('DeskPRO:emails_user:feedback-disapproved.html.twig', $vars);

                    $mailer->send($message);
                }
            }
        );
    }
}
