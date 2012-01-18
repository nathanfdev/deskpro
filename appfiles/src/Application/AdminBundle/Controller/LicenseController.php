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

use DeskPRO\Kernel\License;
use Application\DeskPRO\Entity;
use Application\DeskPRO\App;

use Application\AdminBundle\Form\EditAgentType;
use Application\AdminBundle\FormModel as AdminFormModel;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

use Symfony\Component\Form;

class LicenseController extends AbstractController
{
	protected function init()
	{
		parent::init();
		License::getLicense();
	}

	############################################################################
	# index
	############################################################################

	public function indexAction()
	{
		if (!License::getLicense()->hasLicense()) {
			return $this->redirectRoute('admin_license_reqdemo');
		}

		return $this->render('AdminBundle:License:license.html.twig', array(
			'lic' => License::getLicense()
		));
	}


	############################################################################
	# request-demo
	############################################################################

	public function requestDemoAction()
	{
		$errors = array();
		if ($this->in->getBool('process')) {
			$email_address = $this->in->getString('email_address');
			if (!$email_address || !\Orb\Validator\StringEmail::isValueValid($email_address)) {
				$errors['email'] = true;
			}

			if (!$errors) {

				$client = new \Zend\Http\Client(null, array('timeout' => 15));
				$client->setMethod(\Zend\Http\Request::METHOD_POST);
				$client->setUri(DP_LIC_SERVER . '/license/request-demo.json');
				$client->getRequest()->post()->set('install_key', $this->settings->get('core.install_key'));
				$client->getRequest()->post()->set('email_address', $email_address);
				$client->getRequest()->post()->set('url', App::getRequest()->getBaseUrl());

				$hostname = gethostname();

				if ($hostname) {
					$client->getRequest()->post()->set('hostname', $hostname);

					$ip_address = gethostbyname($hostname);
					if ($ip_address) {
						$client->getRequest()->post()->set('ip_address', $ip_address);
					}
				}

				$failed = false;
				try {
					$result = $client->send();

					if ($result->isServerError()) {
						$failed = 'server_error';
					} elseif ($result->isClientError()) {
						$failed = true;
					} else {
						$data = @json_decode($result->getBody(), true);
						if (!$data) {
							$failed = 'server_error';
						} else {
							if (isset($data['error'])) {
								if ($data['error_code']) {
									$errors['email'] = true;
								} else {
									$errors['request_error'] = $data['error_code'];
								}
							} else {
								return $this->redirectRoute('admin_license_input', array('from_demo' => 1));
							}
						}
					}

				} catch (\Zend\Http\Client\Adapter\Exception $e) {
					if ($e->getCode() == \Zend\Http\Client\Adapter\Exception\TimeoutException::READ_TIMEOUT) {
						$failed = 'timeout';
					} else {
						$failed = true;
					}
				} catch (\Exception $e) {
					$failed = true;
				}

				if ($failed) {
					if ($failed === true) {
						$errors['unknown_request_error'] = true;
					} else {
						$errors[$failed] = true;
					}
				}
			}
		}

		return $this->render('AdminBundle:License:request-demo.html.twig', array(
			'errors' => $errors,
		));
	}


	############################################################################
	# input
	###########################################################################

	public function inputAction()
	{
		$from_demo = $this->in->getBool('from_demo');
		$invalid = $this->in->getString('invalid');
		return $this->render('AdminBundle:License:input.html.twig', array(
			'from_demo' => $from_demo,
			'invalid' => $invalid,
			'currently_has_license' => License::getLicense()->hasLicense()
		));
	}

	public function saveNewLicenseAction()
	{
		$license_code = $this->in->getString('license_code');

		$lic = License::create($license_code, $this->settings->get('core.install_key'));
		if ($lic->isLicenseCodeError()) {
			return $this->redirectRoute('admin_license_input', array('invalid' => $lic->getLicenseCodeError()));
		}

		#------------------------------
		# Check against lic server
		#------------------------------

		if (!$lic->has('no_confirm_license')) {

			// Check it against the license server now
			$client = new \Zend\Http\Client(null, array('timeout' => 15));
			$client->setMethod(\Zend\Http\Request::METHOD_POST);
			$client->setUri(DP_LIC_SERVER . '/license/confirm-demo.json');
			$client->getRequest()->post()->set('license_code', $license_code);
			$client->getRequest()->post()->set('install_key', $this->settings->get('core.install_key'));

			$failed = false;
			try {
				$result = $client->send();

				if ($result->isClientError() || $result->isServerError()) {
					$failed = 'server_error';
				} else {
					$data = @json_decode($result->getBody(), true);
					if (!$data) {
						$failed = 'server_error';
					} else {
						if (isset($data['error'])) {
							$failed = $data['error_code'];
						}
					}
				}
			} catch (\Zend\Http\Client\Adapter\Exception $e) {
				if ($e->getCode() == \Zend\Http\Client\Adapter\Exception\TimeoutException::READ_TIMEOUT) {
					$failed = 'timeout';
				} else {
					$failed = true;
				}
			} catch (\Exception $e) {
				$failed = true;
			}

			if ($failed) {
				if ($failed === true) {
					return $this->redirectRoute('admin_license_input', array('invalid' => 'unknown_request_error'));
				} else {
					return $this->redirectRoute('admin_license_input', array('invalid' => 'req_' . $failed));
				}
			}
		}

		$this->em->getConnection()->beginTransaction();

		try {
			$lic_setting = $this->em->find('DeskPRO:Setting', array('name' => 'core.license'));
			if (!$lic_setting) {
				$lic_setting = new \Application\DeskPRO\Entity\Setting();
				$lic_setting->name = 'core.license';
			}

			$lic_setting->value = $license_code;
			$this->em->persist($lic_setting);
			$this->em->flush();

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		$setup_initial = $this->container->getSetting('core.setup_initial');
		if ($setup_initial < 20) {
			App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.setup_initial', '21');
			return $this->redirectRoute('admin');
		}

		$this->session->setFlash('saved', "License code");
		return $this->redirectRoute('admin_license');
	}
}
