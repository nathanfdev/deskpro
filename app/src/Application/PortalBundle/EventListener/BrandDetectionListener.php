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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\EventListener;

use Application\DeskPRO\Brand\BrandStack;
use Application\DeskPRO\EntityRepository\Brand;
use Application\DeskPRO\Entity\Brand as BrandEntity;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Early in a request, this listener will determine the active brand for this request and push it onto the brand_stack
 * service.
 */
class BrandDetectionListener implements EventSubscriberInterface
{
	/**
	 * @var \Application\DeskPRO\Brand\BrandStack
	 */
	private $brand_stack;

	/**
	 * @var \Application\DeskPRO\NewSettings\SettingsResolver
	 */
	private $settings_resolver;

	/**
	 * @var \Application\DeskPRO\EntityRepository\Brand
	 */
	private $brand_repository;

	/**
	 * @var \Application\DeskPRO\Entity\Brand
	 */
	private $default_brand;


	public function __construct(BrandStack $brand_stack, SettingsResolver $settings_resolver, Brand $brand_repository, BrandEntity $default_brand)
	{
		$this->brand_stack = $brand_stack;
		$this->settings_resolver = $settings_resolver;
		$this->brand_repository = $brand_repository;
		$this->default_brand = $default_brand;
	}

	/**
	 * @param Request $request
	 * @return int|null the brand id detected
	 */
	public function detectBrandInRequest(Request $request)
	{
		if ($brand_id = $request->query->get('brand', null)) {
			return $this->brand_repository->find($brand_id);
		}

		return null;
	}

	public function getDefaultBrand()
	{
		return $this->default_brand;
	}

	public function onKernelRequest(GetResponseEvent $event)
	{
		if (!$event->isMasterRequest()) {
			// only run this on the master request - we only detect once per request.
			return;
		}

		$request = $event->getRequest();

		if (!$brand = $this->detectBrandInRequest($request)) {
			$brand = $this->getDefaultBrand();
		}

		$this->brand_stack->push($brand);
	}

	public static function getSubscribedEvents()
	{
		return array(
			// high priority, must be called BEFORE RouterListener (which is 32)
			KernelEvents::REQUEST => array('onKernelRequest', 33)
		);
	}
}
 