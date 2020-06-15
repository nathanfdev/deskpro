<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio;

use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twilio\Exceptions\TwilioException;

/**
 * Class TwimlAppConfigurator.
 */
class TwimlAppConfigurator
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TwilioAdapter
     */
    private $twilioAdapter;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * Constructor.
     *
     * @param EntityManager   $em
     * @param TwilioAdapter   $twilioAdapter
     * @param RouterInterface $router
     */
    public function __construct(EntityManager $em, TwilioAdapter $twilioAdapter, RouterInterface $router)
    {
        $this->em            = $em;
        $this->twilioAdapter = $twilioAdapter;
        $this->router        = $router;
    }

    /**
     * @param TwilioVoiceAccount $account
     */
    public function createOrUpdateTwimlApp(TwilioVoiceAccount $account)
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

        $twimlApp = $this->twilioAdapter->createOrUpdateTwimlApp($account, $requestUrl, 'GET', $statusUrl, 'POST');
        if (!$twimlApp) {
            throw new TwilioException('Unable to create Twiml app');
        }

        // set proper app sid for account
        $account->setTwimlAppSid($twimlApp->sid);

        $this->em->persist($account);
        $this->em->flush();

        // set proper app sid for account numbers
        foreach ($account->getNumbers() as $number) {
            $this->twilioAdapter->setTwimlAppId($number);
        }
    }
}
