<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 */

namespace Application\DeskPRO\Controller;

use Application\DeskPRO\JobQueue\Processor\IncomingSmsProcessor;
use Application\DeskPRO\Sms\Detector\SmsAccountDetector;
use Symfony\Component\HttpFoundation\Response;

class ChannelIncomingController extends AbstractController
{
    ####################################################################################################################
    # incoming facebook pushes
    ####################################################################################################################

    public function facebookAction()
    {
        file_put_contents(
            '/var/www/html/file.txt', "Time: ".date('j M, Y - h:m:s')."\n----------------------\n".print_r(
                $_REQUEST, true
            )."\n".print_r($_SERVER, true)."\n\n--------------------------------------\n\n", FILE_APPEND
        );

        // responds to challenge - used in setup process
        if (isset($_REQUEST['hub_challenge'])) {
            echo $_REQUEST['hub_challenge'];
            exit;
        }

        $json_string = file_get_contents('php://input');
        $json        = json_decode($json_string, true);

        file_put_contents(
            '/var/www/html/file.txt', "Time: ".date('j M, Y - h:m:s')."\n----------------------\n".print_r(
                $_REQUEST, true
            )."\n".print_r($_SERVER, true)."\n".print_r(
                $json, true
            )."\n\n--------------------------------------\n\n", FILE_APPEND
        );

        return new Response();
    }

    ####################################################################################################################
    # accept Twilio sms messages
    ####################################################################################################################

    public function twilioSmsAction()
    {
        $payload                = array();
        $payload['message']     = $this->request->request->get('Body', '');
        $payload['from_number'] = $this->request->request->get('From');
        $payload['to_number']   = $this->request->request->get('To');

        // try to detect sms
        $sms_account               = $this->findSmsAccountForNumber($payload['to_number']);
        $payload['sms_account_id'] = $sms_account ? $sms_account->id : null;

        // if the text is not FROM a registered Twilio number, add it to the queue
        if (!$fromSmsAccount = $this->findSmsAccountForNumber($payload['from_number'])) {
            $this->getContainer()->getJobQueue()->add(
                IncomingSmsProcessor::JOB_TYPE, $payload
            );
        } else {
            // if the message is this account's verification code, then confirm the account
            if ($fromSmsAccount->test_code == $payload['message']) {
                $fromSmsAccount->is_tested  = true;
                $fromSmsAccount->is_enabled = true;
                $this->getContainer()->getEm()->persist($fromSmsAccount);
                $this->getContainer()->getEm()->flush($fromSmsAccount);
            }
        }

        return $this->createResponse(print_r($payload, true));
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getSmsAccountRepo()
    {
        return $this->getContainer()->getEm()->getRepository('DeskPRO:SmsAccount');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getPhoneNumberRepo()
    {
        return $this->getContainer()->getEm()->getRepository('DeskPRO:PhoneNumber');
    }

    /**
     * @param $to_number
     * @return \Application\DeskPRO\Entity\SmsAccount|null
     */
    private function findSmsAccountForNumber($to_number)
    {
        $detector = new SmsAccountDetector($this->getContainer()->getEm());

        return $detector->detect(null, $to_number);
    }
}
