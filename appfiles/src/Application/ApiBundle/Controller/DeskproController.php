<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\ApiBundle\Controller;

/**
 * A misc resource for doing things like testing if the system is up, or fetching
 * statistics etc.
 */
class DeskproController extends AbstractController
{
	/**
	 * Simple action to return the current server time.
	 */
	public function timeAction()
	{
		return $this->createApiResponse(array(
			'timestamp' => time(),
			'fulldate' => date('r')
		));
	}
	


	/**
	 * Gets the value of a setting.
	 * 
	 * @param string $setting_name
	 */
	public function settingAction($setting_name)
	{
		$value = $this->settings[$setting_name];

		if ($value === null) {
			return $this->createApiErrorResponse('setting_not_found', 'No setting was found with that name', 404);
		}

		return $this->createApiResponse(array('setting_value' => $value));
	}

	

	/**
	 * Sets a new value for a setting
	 *
	 * @param string $setting_name
	 */
	public function postSettingAction($setting_name)
	{
		$current_value = $this->settings[$setting_name];

		if ($current_value === null) {
			return $this->createApiErrorResponse('setting_not_found', 'No setting was found with that name', 404);
		}

		try {
			$setting =$this->em->findOneBy('CoreBundle:Setting', array('name' => $setting_name));
		} catch (\Doctrine\ORM\NoResultException $e) {
			$setting = new \Application\CoreBundle\Entity\Setting();
		}

		$setting['value'] = isset($_POST['value']) ? $_POST['value'] : '';
		$this->em->persist($setting);
		$this->em->flush();

		return $this->createApiResponse(array('success' => 1));
	}
}