<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener\Doctrine;

use DeskPRO\Bundle\AppBundle\Entity\PlivoVoiceAccount;
use DeskPRO\Bundle\VoiceBundle\Plivo\PlivoAdapter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class PlivoAccountListener.
 */
class PlivoAccountListener
{
    /**
     * @var PlivoAdapter
     */
    private $plivoAdapter;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * Constructor.
     *
     * @param PlivoAdapter    $plivoAdapter
     * @param EntityManager   $em
     * @param RouterInterface $router
     */
    public function __construct(PlivoAdapter $plivoAdapter, EntityManager $em, RouterInterface $router)
    {
        $this->plivoAdapter = $plivoAdapter;
        $this->em           = $em;
        $this->router       = $router;
    }

    /**
     * @ORM\PostPersist()
     *
     * @param PlivoVoiceAccount $account
     *
     * @throws \Exception
     */
    public function createApplication(PlivoVoiceAccount $account)
    {
        $answerUserUrl = $this->router->generate('plivo_answer_user_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $answerAgentUrl = $this->router->generate('plivo_answer_agent_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $hangupUserUrl = $this->router->generate('plivo_hangup_user_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $hangupAgentUrl = $this->router->generate('plivo_hangup_agent_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $userAppId = $this->plivoAdapter->createApplication(
            $account,
            'Deskpro User App',
            $answerUserUrl,
            'POST',
            $hangupUserUrl,
            'POST'
        );

        $agentAppId = $this->plivoAdapter->createApplication(
            $account,
            'Deskpro Agent App',
            $answerAgentUrl,
            'POST',
            $hangupAgentUrl,
            'POST'
        );

        if (!$userAppId || !$agentAppId) {
            throw new \RuntimeException('Unable to create Plivo app');
        }

        $this->em->getConnection()->update(
            'voice_accounts',
            [
                'user_application_id'  => $userAppId,
                'agent_application_id' => $agentAppId,
            ],
            [
                'id' => $account->getId(),
            ]
        );
    }
}
