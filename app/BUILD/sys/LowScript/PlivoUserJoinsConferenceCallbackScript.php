<?php

namespace DpSys\LowScript;

use Plivo\XML\Response as PlivoXML;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class PlivoUserJoinsConferenceCallbackScript.
 */
class PlivoUserJoinsConferenceCallbackScript extends LowScriptAbstract
{
    /**
     * {@inheritdoc}
     */
    public function runAction()
    {
        $plivoXml = new PlivoXML();
        if (isset($_REQUEST['callId'])) {
            $q = $this->getPdoRead()->prepare('
              SELECT p.id AS call_id, a.id AS account_id, a.account_auth FROM voice_phone_calls p
              JOIN voice_numbers n ON p.number_id = n.id
              JOIN voice_accounts a ON n.account_id = a.id
              WHERE p.id = ?
            ');
            $q->execute([$_GET['callId']]);

            $phoneCall = $q->fetch(\PDO::FETCH_ASSOC);
            if ($phoneCall) {
                $container = $this->bootFullSystem();

                $conferenceStatusUrl = $container->getRouter()->generate('plivo_conference_status_callback', [
                    'account'     => $phoneCall['account_id'],
                    'accountAuth' => $phoneCall['account_auth'],
                    'phoneCall'   => $phoneCall['call_id'],
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                $plivoXml->addConference('conference'.$phoneCall['call_id'], [
                    'endConferenceOnExit' => false,
                    'callbackUrl'         => $conferenceStatusUrl,
                    'callbackMethod'      => 'POST',
                    'record'              => true,
                ]);
            }
        }

        header('Content-Type: text/xml');
        echo $plivoXml->toXML();
    }
}
