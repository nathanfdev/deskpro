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

namespace Application\PortalBundle\Request;


use Application\DeskPRO\ORM\EntityManager;
use Application\PortalBundle\HttpKernel\Exception\PermanentRedirectException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Used to keep legacy slug urls working (1-slug-here) and forward them to the new (slug-here)
 */
class DeskproSlugConverter implements ParamConverterInterface
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */
	private $em;


	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	public function apply(Request $request, ParamConverter $configuration)
	{
		$param_name       = $configuration->getName();
		$param_class      = $configuration->getClass();
		$param_options    = $this->getOptions($configuration);
		$slug_attribute_name = $param_options['slug_route_param'];
		$slug_route_param = $request->attributes->get($slug_attribute_name);
		$slug_col         = $param_options['slug_col'];

		$repo = $this->em->getRepository($param_class);
		if ($obj = $repo->findOneBy(array($slug_col => $slug_route_param))) {
			$request->attributes->set($param_name, $obj);

			return;
		}


		$id = substr($slug_route_param, 0, strpos($slug_route_param, '-'));
		if ($obj = $repo->find($id)) {
			// it exists and we are on the old url at the moment, lets flag a 301 response
			$new_slug_attribute = array(
				$slug_attribute_name => $obj->slug
			);

			throw new PermanentRedirectException(
				$request->attributes->get('_route'),
				array_merge(
					$request->attributes->get('_route_params'),
					$new_slug_attribute
				)
			);
		}

		throw new NotFoundHttpException(sprintf('could not find a "%s" for the slug value found in the route variable "%s" (value: %s)', $param_class, $slug_attribute_name, $slug_route_param));
	}

	public function supports(ParamConverter $configuration)
	{
		return true;
	}

	protected function getOptions(ParamConverter $configuration)
	{
		return array_replace(
			array(
				'slug_col' => 'slug',
				'slug_route_param' => 'slug',
			), $configuration->getOptions()
		);
	}
}
 