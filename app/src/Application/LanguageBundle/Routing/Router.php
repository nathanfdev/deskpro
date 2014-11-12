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
 * @package    DeskPRO
 * @subpackage Portal
 */

namespace Application\LanguageBundle\Routing;


use Application\DeskPRO\Entity\Language;
use Application\LanguageBundle\Language\LanguageManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router as BaseRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class Router implements WarmableInterface, RouterInterface, RequestMatcherInterface
{
	/**
	 * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
	 */
	private $router;

	/**
	 * @var \Application\LanguageBundle\Language\LanguageManager
	 */
	private $language_manager;


	public function __construct(BaseRouter $router, LanguageManager $language_manager)
	{
		$this->router           = $router;
		$this->language_manager = $language_manager;
	}


	/**
	 * {@inheritdoc}
	 */
	public function matchRequest(Request $request)
	{
		// TODO: clean this up a bit, and if it is not a GET then don't redirect
		$language_stack = $this->language_manager->getLanguageStack();
		$extractor      = new UrlMatcher();
		$split          = $extractor->extractLanguageCode($request->getPathInfo());

		// there IS a potential language in the url path
		if ($code = $split['language_code']) {
			if (!$this->language_manager->isMultiLanguagePortal()) {
				$this->throwRedirectExceptionTo(null, $split['remaining_pathinfo']);
			}

			if (!$url_language = $this->language_manager->getLanguage($code)) {
				// no language exists and enabled in this system
				$this->throwRedirectExceptionTo(null, $split['remaining_pathinfo']);
			}

			$language_stack->push($url_language);
			return $this->router->match($split['remaining_pathinfo']);
		}

		if (!$this->language_manager->isMultiLanguagePortal()) {
			$language_stack->pushDefault();
			return $this->router->match($split['remaining_pathinfo']);
		}

		// now we know its a multi lang desk and there was no long code, so we need to decide about where to redirect:

		// 1. impl. session last_lang
		// 2. authorized persons preference
		// 3. failing that, negotiate with http.lang
		// else:
		$language_stack->pushDefault();
		$this->throwRedirectExceptionTo($language_stack->getActive(), $split['remaining_pathinfo']);
	}


	/**
	 * {@inheritdoc}
	 */
	public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
	{
		$generated = $this->router->generate($name, $parameters, $referenceType);

		if (!$this->language_manager->isMultiLanguagePortal()) {
			return $generated;
		}

		$language_stack = $this->language_manager->getLanguageStack();
		if (!$language_stack->getActive()) {
			$language_stack->pushDefault();
		}

		// TODO: note the $referenceType - we need to do much more involved url inspection/manipulation
		switch ($referenceType) {
			case self::ABSOLUTE_PATH:
				return sprintf(
					'/%s%s',
					$this->language_manager->getLanguageStack()->getActive()->getUrlPrefix(),
					$generated
				);

			default:
				throw new \InvalidArgumentException(
					'we only support generating ABSOLUTE PATH urls at this time, see LanguageBundle\'s Router'
				);
		}
	}


	/**
	 * Throw an exception that will be caught by our kernel.exception listener
	 *
	 * @param Language $language
	 * @param          $url
	 * @throws RedirectToUrlException
	 */
	protected function throwRedirectExceptionTo(Language $language = null, $url)
	{
		$pre = $language ? '/' . $language->getTwoLetterLanguageCode() : '';
		$url = $pre . $url;
		throw new RedirectToUrlException($url);
	}


	/**
	 * {@inheritdoc}
	 */
	public function setContext(RequestContext $context)
	{
		$this->router->setContext($context);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getContext()
	{
		return $this->router->getContext();
	}


	/**
	 * {@inheritdoc}
	 */
	public function match($path_info)
	{
		return $this->matchRequest(Request::create($path_info));
	}


	/**
	 * {@inheritdoc}
	 */
	public function warmUp($cacheDir)
	{
		$this->router->warmUp($cacheDir);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getRouteCollection()
	{
		return $this->router->getRouteCollection();
	}
}
