<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Community;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Translate\Translate;
use Application\EmailBundle\SwiftMailer\Mailer;
use Application\EmailBundle\SwiftMailer\MailerUtils;
use DeskPRO\Bundle\AppBundle\DataService\Community\CommunityDataService;
use DeskPRO\Bundle\SendmailBundle\Factory\UserViewModelFactory;
use Doctrine\ORM\EntityManager;
use DpSys\Features;

class CommunityTopicModerate implements PersonContextInterface
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
     * @var CommunityDataService
     */
    protected $communityDataService;

    /**
     * @var \DeskPRO\Bundle\BrandBundle\Brand\BrandStack
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
        $this->communityDataService = $container->get('data.community');
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
     * @param \Application\DeskPRO\Entity\CommunityTopic $communityTopic
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function approveCommunityTopic(CommunityTopic $communityTopic)
    {
        $becameReviewed = false;
        if ($communityTopic->getStatus() === CommunityTopic::STATUS_HIDDEN) {
            $statusCategory = $this->communityDataService->getCommunityFirstStatusCategoryByType();
            $communityTopic
                ->setStatus(CommunityTopic::STATUS_ACTIVE)
                ->setStatusCategory($statusCategory);
        } else {
            $becameReviewed = true;
            $communityTopic->setIsReviewed(true);
        }

        $this->em->getConnection()->beginTransaction();
        try {
            $this->em->persist($communityTopic);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }

        if ($becameReviewed) {
            $this->brandStack->pushTemporary(
                $communityTopic->getBrand(),
                function () use ($communityTopic) {
                    if ($this->featureFlags->hasBeta('email_templates')) {
                        $viewModel = $this->userViewmodelFactory->createCommunityTopicApprovedModel($communityTopic, $this->personContext);
                        $this->mailerUtils->sendModelWithPersonContext($communityTopic->getPerson(), $viewModel, ['to' => $communityTopic->getPerson()]);
                    } else {
                        $vars = [
                            'topic' => $communityTopic,
                            'agent' => $this->personContext,
                        ];
                        $message = $this->mailer->createMessage();
                        $message->setToPerson($communityTopic->getPerson());
                        $message->setTemplate('DeskPRO:emails_user:community-topic-approved.html.twig', $vars);

                        $this->mailerUtils->sendWithPersonContext($message, $communityTopic->getPerson(), $communityTopic->getBrand());
                    }
                }
            );
        }
    }

    /**
     * @param CommunityTopic $communityTopic
     * @param string         $reason
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function disapproveCommunityTopic(CommunityTopic $communityTopic, $reason = '')
    {
        if (!$reason) {
            $reason = null;
        }

        $this->em->getConnection()->beginTransaction();
        try {
            $this->em->remove($communityTopic);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }

        $this->brandStack->pushTemporary(
            $communityTopic->getBrand(),
            function () use ($communityTopic, $reason) {
                if ($this->featureFlags->hasBeta('email_templates')) {
                    $viewModel = $this->userViewmodelFactory->createCommunityTopicDisapprovedModel($communityTopic, $this->personContext, $reason);
                    $this->mailerUtils->sendModelWithPersonContext($communityTopic->getPerson(), $viewModel, ['to' => $communityTopic->getPerson()]);
                } else {
                    $vars = [
                        'topic'  => $communityTopic,
                        'agent'  => $this->personContext,
                        'reason' => $reason,
                    ];
                    $message = $this->mailer->createMessage();
                    $message->setToPerson($communityTopic->getPerson());
                    $message->setTemplate('DeskPRO:emails_user:community-topic-disapproved.html.twig', $vars);

                    $this->mailerUtils->sendWithPersonContext($message, $communityTopic->getPerson(), $communityTopic->getBrand());
                }
            }
        );
    }
}
