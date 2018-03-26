<?php

namespace DeskPRO\Bundle\PortalBundle\Theme\TagHandler;

use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use DeskPRO\Bundle\PortalBundle\Theme\Tag;
use DeskPRO\Bundle\PortalBundle\Theme\TagHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class InlineTagHandler.
 */
class InlineTagHandler implements TagHandlerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Tag $tag, TagRequest $tagRequest)
    {
        $tagRequest->attributes->set('_controller', $tag->getControllerName());

        return $this->container->get('http_kernel')->handle($tagRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tag $tag, TagRequest $tagRequest)
    {
        return true;
    }
}
