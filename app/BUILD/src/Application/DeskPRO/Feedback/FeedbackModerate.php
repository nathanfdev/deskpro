<?php

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
use Application\EmailBundle\SwiftMailer\MailerUtils;
use DeskPRO\Bundle\AppBundle\DataService\Feedback\FeedbackDataService;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\SendmailBundle\Factory\UserViewModelFactory;
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
     * @var BrandStack
     */
    protected $brandStack;

    /**
     * @var Features
     */
    protected $featureFlags;

    /**
     * @var UserViewModelFactory
     */
    protected $userViewmodelFactory;

    /**
     * @var MailerUtils
     */
    protected $mailerUtils;

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
        $this->mailerUtils          = $container->get('mailer.utils');
        $this->brandStack           = $container->get('brand_stack');

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
            $mailerUtils      = $this->mailerUtils;

            if ($featureFlags->hasBeta('email_templates')) {
                $viewModel = $this->brandStack->pushTemporary(
                    $feedback->getPerson()->getBrands()->first(),
                    function () use ($viewModelFactory, $feedback, $agent) {
                        return $viewModelFactory->createFeedbackApprovedModel($feedback, $agent);
                    }
                );
                $mailerUtils->sendModelWithPersonContext($feedback->getPerson(), $viewModel, ['to' => $feedback->getPerson()]);
            } else {
                $vars = [
                    'feedback' => $feedback,
                    'agent'    => $agent,
                ];
                $message = $mailer->createMessage();
                $message->setToPerson($feedback->getPerson());
                $message->setTemplate('DeskPRO:emails_user:feedback-approved.html.twig', $vars);

                $mailerUtils->sendWithPersonContext($feedback->getPerson(), $message);
            }
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
        $mailerUtils      = $this->mailerUtils;

        if ($featureFlags->hasBeta('email_templates')) {
            $viewModel = $viewModelFactory->createFeedbackDisapprovedModel($feedback, $agent, $reason);
            $mailerUtils->sendModelWithPersonContext($feedback->getPerson(), $viewModel, ['to' => $feedback->getPerson()]);
        } else {
            $vars = [
                'feedback' => $feedback,
                'agent'    => $agent,
                'reason'   => $reason,
            ];
            $message = $mailer->createMessage();
            $message->setToPerson($feedback->getPerson());
            $message->setTemplate('DeskPRO:emails_user:feedback-disapproved.html.twig', $vars);

            $mailerUtils->sendWithPersonContext($feedback->getPerson(), $message);
        }
    }
}
