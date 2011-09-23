<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * Manages ip and email banning
 */
class BanningController extends AbstractController
{
	public function listAction()
	{
		$banned_ips    = App::getEntityRepository('DeskPRO:BanIp')->getList();
		$banned_emails = App::getEntityRepository('DeskPRO:BanEmail')->getList();

		return $this->render('AdminBundle:Banning:list.html.twig', array(
			'banned_ips'    => $banned_ips,
			'banned_emails' => $banned_emails
		));
	}

	public function newIpBanAction()
	{
		$ip_address = $this->in->getString('ip');

		$ipban = new Entity\BanIp();
		$ipban['banned_ip'] = $ip_address;

		App::getOrm()->persist($ipban);
		App::getOrm()->flush();

		return $this->render('AdminBundle:Banning:ip-row.html.twig', array(
			'ip' => $ipban['banned_ip']
		));
	}

	public function newEmailBanAction()
	{
		$email_address = $this->in->getString('email');

		$emailban = new Entity\BanEmail();
		$emailban['banned_email'] = $email_address;

		App::getOrm()->persist($emailban);
		App::getOrm()->flush();

		return $this->render('AdminBundle:Banning:email-row.html.twig', array(
			'email' => $emailban['banned_email']
		));
	}

	public function removeIpBanAction()
	{
		$ip_address = $this->in->getString('ip');

		$ipban = App::getEntityRepository('DeskPRO:BanIp')->find($ip_address);

		if ($ipban) {
			App::getOrm()->remove($ipban);
			App::getOrm()->flush();
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'ip' => $ip_address
		));
	}

	public function removeEmailBanAction()
	{
		$email_address = $this->in->getString('email');

		$emailban = App::getEntityRepository('DeskPRO:BanEmail')->find($email_address);

		if ($emailban) {
			App::getOrm()->remove($emailban);
			App::getOrm()->flush();
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'email' => $email_address
		));
	}
}
