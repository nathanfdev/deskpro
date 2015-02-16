<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 */

namespace Application\PortalBundle\Theme\TagHandler;

use Application\DeskPRO\Domain\DomainObject;
use Application\PortalBundle\HttpCache\PortalCacheHelper;
use Application\PortalBundle\Request\TagRequest;
use Application\PortalBundle\Theme\Tag;
use Application\PortalBundle\Theme\TagHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\EsiFragmentRenderer;

class EsiTagHandler implements TagHandlerInterface
{
    /**
     * @var PortalCacheHelper
     */
    private $portal_cache_helper;

    /**
     * @var EsiFragmentRenderer
     */
    private $esi_renderer;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container, PortalCacheHelper $portal_cache_helper)
    {
        $this->portal_cache_helper = $portal_cache_helper;
        $this->container = $container;
    }

    public function supports(Tag $tag, TagRequest $tag_request)
    {
        return $tag->isEsi($this->portal_cache_helper->isGuestRequest());
    }

    public function handle(Tag $tag, TagRequest $tag_request)
    {
        $this->filterRequest($tag_request);

        return $this->container->get('fragment.renderer.esi')->render(
            new ControllerReference($tag->getControllerName(), $tag_request->attributes->all(), $tag_request->query->all()),
            $tag_request,
            array('ignore_errors' => true)
        );
    }

    /**
     * @param TagRequest $tag_request
     */
    private function filterRequest(TagRequest $tag_request)
    {
        $new_attrs = $this->filterOutObjects($tag_request->attributes);
        $tag_request->attributes->replace(
            $new_attrs
        );
        $new_query = $this->filterOutObjects($tag_request->query);
        $tag_request->query->replace(
            $new_query
        );
    }

    private function filterOutObjects(ParameterBag $bag)
    {
        $new_params = array();
        foreach ($bag->all() as $key => $val) {
            if ('visitor_id' === $key) {
                continue; // ignore visitor_id in ESI urls
            }

            if (is_object($val)) {
                if (!$val instanceof DomainObject) {
                    continue;
                }
                $val = $val->getId();
            }
            $new_params[$key] = $val;
        }

        return $new_params;
    }
}
