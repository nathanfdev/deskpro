<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

namespace Application\DeskPRO\Service;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\EntityRepository\WhiteListedIp;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


/**
 * Class CheckWhitelistedIP
 * @package Application\DeskPRO\Service
 */
class CheckWhitelistedIP
{
	/**
	 * TODO should be moved to security layer
	 * check if IP of agent/admin is whitelisted
	 * @param DeskproContainer $container
	 * @param Person $person
	 * @return bool
	 */
	static public function checkIP(DeskproContainer $container, Person $person = null)
	{
		if (!$container->getSetting('agent.ip_security.enabled')) {
			return true;
		}

		if (!$person || !$person['is_agent']) {
			return true;
		}

		$mode = $container->getSetting('agent.ip_security.mode');
		$enabledForAgents = false !== strpos($mode, 'agents');
		$enabledForAdmins = false !== strpos($mode, 'admins') && $person['can_admin'];
		if (!$enabledForAgents && !$enabledForAdmins) {
			return true;
		}

		/** @var WhiteListedIp $rep */
		$rep = $container->getEm()->getRepository('DeskPRO:WhiteListedIp');
		if (in_array(dp_get_user_ip_address(), $rep->getIpsForPerson($person))) {
			return true;
		}

		$code_data = TmpData::create(
			'whitelist-ip', array('person_id' => $person['id'], 'interface' => DP_INTERFACE), '+40 minutes'
		);
		$code_data->setData('ip', dp_get_user_ip_address());
		$container->getEm()->persist($code_data);
		$container->getEm()->flush();

		$url = $container->get('router')->generate(
			'agent_whitelist_ip', array('code' => $code_data->getCode()), UrlGeneratorInterface::ABSOLUTE_URL
		);
		$vars = array(
			'ip'		=> dp_get_user_ip_address(),
			'code'		=> $code_data->getCode(),
			'person'	=> $person,
			'interface'	=> DP_INTERFACE,
			'url'		=> $url,
		);

		$message = $container->getMailer()->createMessage();
		$message->setTemplate('DeskPRO:emails_agent:whitelist-ip.html.twig', $vars);
		$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
		$message->disableQueueHint();

		$container->getMailer()->send($message);
		return false;
	}
} 