<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice;

use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\VoiceBundle\Twilio\TwilioAdapter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twilio\Exceptions\TwilioException;

/**
 * Class VoiceAccountListener.
 */
class VoiceAccountListener
{
    /**
     * @var TwilioAdapter
     */
    private $twilioAdapter;

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
     * @param TwilioAdapter   $twilioAdapter
     * @param EntityManager   $em
     * @param RouterInterface $router
     */
    public function __construct(TwilioAdapter $twilioAdapter, EntityManager $em, RouterInterface $router)
    {
        $this->twilioAdapter = $twilioAdapter;
        $this->em            = $em;
        $this->router        = $router;
    }

    /**
     * @ORM\PostPersist()
     *
     * @param VoiceAccount $account
     *
     * @throws TwilioException
     */
    public function createTwimlApp(VoiceAccount $account)
    {
        // create twiml app
        $requestUrl = $this->router->generate('twilio_phone_number_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $statusUrl = $this->router->generate('twilio_phone_number_status_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $twimlApp = $this->twilioAdapter->createTwimlApp($account, $requestUrl, 'GET', $statusUrl, 'POST');
        if (!$twimlApp) {
            throw new TwilioException('Unable to create Twiml app');
        }

        $this->em->getConnection()->update(
            'voice_accounts',
            [
                'twiml_app_sid' => $twimlApp->sid,
            ],
            [
                'id' => $account->getId(),
            ]
        );
    }

    /**
     * @ORM\PreRemove()
     *
     * @param VoiceAccount $account
     */
    public function onRemove(VoiceAccount $account)
    {
        // disable agent voice flags cause all their workers were deleted as well
        $qb = $this->em->createQueryBuilder();
        $qb
            ->update(AgentData::class, 'a')
            ->set('a.isVoiceEnabled', 0)
            ->getQuery()
            ->execute()
        ;

        // clear voice targets
        $this->em->getConnection()->executeQuery('DELETE FROM voice_targets');
    }
}
