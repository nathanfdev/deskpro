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

use Application\DeskPRO\Entity;
use Application\DeskPRO\App;
use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

class UserRegController extends AbstractController
{
	############################################################################
	# options
	############################################################################

	public function optionsAction()
	{
		return $this->render('AdminBundle:UserReg:options.html.twig');
	}

	public function saveOptionsAction()
	{
		App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.user_mode', $this->in->getString('mode'));
		App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.reg_url', $this->in->getString('reg_url'));
		App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.email_validation', $this->in->getBool('email_validation'));
		App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.existing_account_login', $this->in->getBool('existing_account_login'));

		return $this->createJsonResponse(array('success'=> true));
	}


	############################################################################
	# facebook
	############################################################################

	public function facebookEditAction()
	{
		$facebook = $this->em->getRepository('DeskPRO:Usersource')->getByType('facebook');
		if (!$facebook) {
			$facebook = new \Application\DeskPRO\Entity\Usersource();
			$facebook->is_enabled = false;
			$facebook->source_type = 'facebook';
			$facebook->title = 'Facebook';

			$this->em->getConnection()->beginTransaction();
			try {
				$this->em->persist($facebook);
				$this->em->flush();
				$this->em->getConnection()->commit();
			} catch (\Exception $e) {
				$this->em->getConnection()->rollback();
				throw $e;
			}
		}

		if ($this->in->getBool('process')) {
			$this->ensureRequestToken('facebook_setup');

			$facebook->setOptions(array(
				'app_key' => $this->in->getString('facebook.app_key'),
				'app_secret' => $this->in->getString('facebook.app_secret'),
			));

			$facebook->is_enabled = true;

			$this->em->getConnection()->beginTransaction();
			try {
				$this->em->persist($facebook);
				$this->em->flush();

				$this->em->getRepository('DeskPRO:Setting')->updateSetting('core.facebook_source_enabled', (int)$facebook->is_enabled);

				$this->em->getConnection()->commit();
				return $this->redirectRoute('admin_userreg_options');
			} catch (\Exception $e) {
				$this->em->getConnection()->rollback();
				throw $e;
			}
		}

		return $this->render('AdminBundle:UserReg:facebook-edit.html.twig', array(
			'usersource' => $facebook,
			'options' => $facebook->options
		));
	}

	public function facebookToggleAction()
	{
		$facebook = $this->em->getRepository('DeskPRO:Usersource')->getByType('facebook');
		if (!$facebook || $facebook->hasOption('is_setup')) {
			return $this->redirectRoute('admin_userreg_facebook_edit');
		}

		if ($facebook->is_enabled) {
			$facebook->is_enabled = false;
		} else {
			$facebook->is_enabled = true;
		}

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->persist($facebook);
			$this->em->flush();

			$this->em->getRepository('DeskPRO:Setting')->updateSetting('core.facebook_source_enabled', (int)$facebook->is_enabled);

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_userreg_options');
	}

	############################################################################
	# google
	############################################################################

	public function googleToggleAction()
	{
		$google = $this->em->getRepository('DeskPRO:Usersource')->getByType('google');
		if (!$google) {
			$google = new \Application\DeskPRO\Entity\Usersource();
			$google->is_enabled = false;
			$google->source_type = 'google';
			$google->title = 'Google';
		}

		if ($google->is_enabled) {
			$google->is_enabled = false;
		} else {
			$google->is_enabled = true;
		}

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->persist($google);
			$this->em->flush();

			$this->em->getRepository('DeskPRO:Setting')->updateSetting('core.google_source_enabled', (int)$google->is_enabled);

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_userreg_options');
	}

}
