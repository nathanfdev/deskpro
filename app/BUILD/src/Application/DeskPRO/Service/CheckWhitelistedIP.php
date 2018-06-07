<?php

namespace Application\DeskPRO\Service;

use Application\AgentBundle\Controller\MainController;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\EntityRepository\WhiteListedIp;
use Application\DeskPRO\HttpKernel\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class CheckWhitelistedIP.
 */
class CheckWhitelistedIP
{
    /**
     * TODO should be moved to security layer
     * check if IP of agent/admin is whitelisted.
     *
     * @param Request          $request
     * @param DeskproContainer $container
     * @param Person           $person
     * @param Controller       $controller
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     *
     * @return bool
     */
    public static function checkIP(Request $request, DeskproContainer $container, Person $person = null, $controller = null)
    {
        if (!$container->getSetting('agent.ip_security.enabled')) {
            return true;
        }

        if (!$person || !$person['is_agent']) {
            return true;
        }

        $mode             = $container->getSetting('agent.ip_security.mode');
        $enabledForAgents = false !== strpos($mode, 'agents');
        $enabledForAdmins = false !== strpos($mode, 'admins') && $person['can_admin'];
        if (!$enabledForAgents && !$enabledForAdmins) {
            return true;
        }

        $ip = $request->getClientIp();

        /** @var WhiteListedIp $rep */
        $rep = $container->getEm()->getRepository('DeskPRO:WhiteListedIp');
        if (in_array($ip, $rep->getIpsForPerson($person))) {
            return true;
        }

        $codeData = TmpData::create(
            'whitelist-ip', ['person_id' => $person['id'], 'interface' => DP_INTERFACE], '+40 minutes'
        );
        $codeData->setData('ip', $ip);
        $container->getEm()->persist($codeData);
        $container->getEm()->flush();

        if ($controller && $controller instanceof MainController) {
            $url = $container->get('router')->generate(
                'agent_whitelist_ip', ['code' => $codeData->getCode()], UrlGeneratorInterface::ABSOLUTE_URL
            );
            $vars = [
                'ip'        => $ip,
                'code'      => $codeData->getCode(),
                'person'    => $person,
                'interface' => DP_INTERFACE,
                'url'       => $url,
            ];

            if ($container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                $viewModel = $container->get('email.agent_viewmodel_factory')
                    ->createAgentWhitelistIpModel($url);
                $container->get('email.email_sender')
                    ->send($viewModel, ['to' => $person]);
            } else {
                $message = $container->getMailer()->createMessage();
                $message->setTemplate('DeskPRO:emails_agent:whitelist-ip.html.twig', $vars);
                $message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());

                $container->getMailer()->send($message);
            }
        }

        return false;
    }
}
