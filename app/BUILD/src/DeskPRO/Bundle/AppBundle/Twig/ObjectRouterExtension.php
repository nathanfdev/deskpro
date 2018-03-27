<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Twig;

use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;

/**
 * Class ObjectRouterExtension.
 */
class ObjectRouterExtension extends \Twig_Extension
{
    /**
     * @var ObjectRouter
     */
    private $objectRouter;

    /**
     * Constructor.
     *
     * @param ObjectRouter $objectRouter
     */
    public function __construct(ObjectRouter $objectRouter)
    {
        $this->objectRouter = $objectRouter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'portal_path',
                [$this, 'generatePortalPath']
            ),
            new \Twig_SimpleFunction(
                'portal_url',
                [$this, 'generatePortalUrl']
            ),
            new \Twig_SimpleFunction(
                'agent_path',
                [$this, 'generateAgentPath']
            ),
            new \Twig_SimpleFunction(
                'agent_url',
                [$this, 'generateAgentUrl']
            ),
        ];
    }

    public function generatePortalPath($object, $type = null, array $extra_params = [])
    {
        return $this->objectRouter->getPortalPath($object, $type, $extra_params);
    }

    public function generatePortalUrl($object, $type = null, array $extra_params = [])
    {
        return $this->objectRouter->getPortalUrl($object, $type, $extra_params);
    }

    public function generateAgentPath($object, $type = null, array $extra_params = [])
    {
        return $this->objectRouter->getAgentPath($object, $type, $extra_params);
    }

    public function generateAgentUrl($object, $type = null, array $extra_params = [])
    {
        return $this->objectRouter->getAgentUrl($object, $type, $extra_params);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'object_router';
    }
}
