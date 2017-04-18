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

namespace Cloud\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\TmpData;
use Application\LegacyApiBundle\Controller\AbstractController;
use DpSys\License;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CloudCallController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public function preActionHandler(Request $request, $action, $arguments = null)
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        if (!isset($_REQUEST['DPC_CALL_KEY']) || !$DP_ENV->getConfig('cloud.DPC_CALL_KEY') || $DP_ENV->getConfig('cloud.DPC_CALL_KEY') != $_REQUEST['DPC_CALL_KEY']) {
            return $this->createApiErrorResponse('invalid_call_key', 'Invalid call key', 403);
        }

        return null;
    }

    public function pingAction()
    {
        return $this->createJsonResponse(['time' => time()]);
    }

    public function resetPasswordAction($person_id)
    {
        /** @var $person \Application\DeskPRO\Entity\Person */
        $person = $this->em->find('DeskPRO:Person', $person_id);

        if (!$person) {
            throw $this->createNotFoundException();
        }

        $email = $person->getPrimaryEmailAddress();

        //------------------------------
        // Send reset as a link
        //------------------------------

        if ($this->in->getBool('link')) {
            $interface = 'agent';
            if (License::getLicense()->isPastExpireDate() || DPC_BILL_FAILED) {
                $interface = 'billing';
            }

            $codeData = TmpData::create('reset-password', ['person_id' => $person['id'], 'interface' => $interface], '+3 days');
            $this->em->persist($codeData);
            $this->em->flush();

            if ($this->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                $resetCode = $codeData->getCode();
                if ($person->isAgent()) {
                    if ($interface == 'billing') {
                        $resetUrl = $this->get('router')->generate('billing_login', ['reset_code' => $resetCode], UrlGeneratorInterface::ABSOLUTE_URL);
                    } else {
                        $resetUrl = $this->get('router')->generate('agent_login', ['reset_code' => $resetCode], UrlGeneratorInterface::ABSOLUTE_URL);
                    }
                } else {
                    $resetUrl = $this->get('router')->generate('user_login_resetpass_newpass', ['code' => $resetCode], UrlGeneratorInterface::ABSOLUTE_URL);
                }
                $viewModel = $this->get('email.user_viewmodel_factory')
                    ->createResetPasswordModel($resetUrl);
                $this->get('email.email_sender')
                    ->send($viewModel, ['to' => $person]);
            } else {
                $vars = [
                    'code'      => $codeData->getCode(),
                    'person'    => $person,
                    'email'     => $email,
                    'interface' => $interface,
                ];

                $message = $this->container->getMailer()->createMessage();
                $message->setTemplate('DeskPRO:emails_user:reset-password.html.twig', $vars);
                $message->setTo($email, $person->getDisplayName());

                $this->container->getMailer()->send($message);
            }

            return $this->createJsonResponse(['sent_reset_link' => $email]);

        //------------------------------
        // Reset password
        //------------------------------
        } else {
            $newPass = $this->in->getString('password');

            if (!$newPass) {
                return $this->createJsonResponse(['error' => 'no_pass']);
            }

            $person->setPassword($newPass);

            return $this->createJsonResponse(['reset_password' => $email]);
        }
    }
}
