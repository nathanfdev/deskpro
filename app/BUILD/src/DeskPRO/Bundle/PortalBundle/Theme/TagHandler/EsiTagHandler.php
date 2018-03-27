<?php

namespace DeskPRO\Bundle\PortalBundle\Theme\TagHandler;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\PortalBundle\HttpCache\PortalCacheHelper;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use DeskPRO\Bundle\PortalBundle\Theme\Tag;
use DeskPRO\Bundle\PortalBundle\Theme\TagHandlerInterface;
use DeskPRO\Component\Util\EntityUtils;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

/**
 * Class EsiTagHandler.
 */
class EsiTagHandler implements TagHandlerInterface
{
    /**
     * @var PortalCacheHelper
     */
    private $portalCacheHelper;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var PortalModeStorage
     */
    private $modeStorage;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param PortalCacheHelper  $portalCacheHelper
     * @param PortalModeStorage  $modeStorage
     */
    public function __construct(
        ContainerInterface $container,
        PortalCacheHelper $portalCacheHelper,
        PortalModeStorage $modeStorage
    ) {
        $this->portalCacheHelper = $portalCacheHelper;
        $this->container         = $container;
        $this->modeStorage       = $modeStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tag $tag, TagRequest $tagRequest)
    {
        return $tag->isEsi($this->portalCacheHelper->isGuestRequest());
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Tag $tag, TagRequest $tagRequest)
    {
        $this->filterRequest($tagRequest);

        try {
            return $this->container->get('fragment.renderer.esi')->render(
                new ControllerReference(
                    $tag->getControllerName(),
                    $tagRequest->attributes->all(),
                    $tagRequest->query->all()
                ),
                $tagRequest,
                ['ignore_errors' => false]
            );
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e, false, null, true);

            return new Response('');
        }
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

    /**
     * @param ParameterBag $bag
     *
     * @return array
     */
    private function filterOutObjects(ParameterBag $bag)
    {
        $new_params = [];
        foreach ($bag->all() as $key => $val) {
            if ('visitor_id' === $key) {
                continue; // ignore visitor_id in ESI urls
            }

            if (is_object($val)) {
                if (!($val instanceof DomainObject || $val instanceof EntityInterface)) {
                    continue;
                }
                $val = EntityUtils::getIdentifier($val);
            }
            $new_params[$key] = $val;
        }

        return $new_params;
    }
}
