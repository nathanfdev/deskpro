<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\LanguageBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Application\LanguageBundle\Routing\RedirectToUrlException;


/**
 * If anyone throws a RedirectToUrlException, we catch it here to return a redirect response to the kernel.
 */
class RedirectExceptionListener implements EventSubscriberInterface
{
	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger        = $logger;
	}


	public function onKernelException(GetResponseForExceptionEvent $event)
	{
		$e = $event->getException();

		// only interested in a particular exception here
		if (!$e instanceof RedirectToUrlException) {
			return;
		}

		$url = $e->getUrl();
		$this->logger->info('RedirectToUrlException caught: 302 redirecting to "' . $url . '"');

		$event->setResponse(new RedirectResponse($url, Response::HTTP_FOUND));
		$event->stopPropagation();
	}


	public static function getSubscribedEvents()
	{
		return array(
			KernelEvents::EXCEPTION => array('onKernelException', 129) // very high priority
		);
	}
}
 