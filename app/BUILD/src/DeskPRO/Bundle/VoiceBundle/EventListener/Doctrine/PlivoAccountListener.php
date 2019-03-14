<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener\Doctrine;

use Application\DeskPRO\NewSettings\SettingsResolver;
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
    // These app names must be constant. When configuring Plivo,
    // we scan existing apps to see if they already exist.
    const AGENT_APP_NAME = 'Deskpro Agent App';
    const USER_APP_NAME  = 'Deskpro User App';

    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

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
     * @param SettingsResolver $settingsResolver
     * @param PlivoAdapter     $plivoAdapter
     * @param EntityManager    $em
     * @param RouterInterface  $router
     */
    public function __construct(SettingsResolver $settingsResolver, PlivoAdapter $plivoAdapter, EntityManager $em, RouterInterface $router)
    {
        $this->settingsResolver = $settingsResolver;
        $this->plivoAdapter     = $plivoAdapter;
        $this->em               = $em;
        $this->router           = $router;
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

        $deskproUrl    = $this->settingsResolver->getGlobalSettings()->get('core.deskpro_url');
        $accountSuffix = ' ('.$account->getAccountId().')';
        $urlSuffix     = ' ('.$deskproUrl.')';

        $userAppId = $this->plivoAdapter->createApplication(
            $account,
            self::USER_APP_NAME.$accountSuffix.$urlSuffix,
            $answerUserUrl,
            'POST',
            $hangupUserUrl,
            'POST'
        );

        $agentAppId = $this->plivoAdapter->createApplication(
            $account,
            self::AGENT_APP_NAME.$accountSuffix.$urlSuffix,
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
