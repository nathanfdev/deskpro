<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\ObjectRouter;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * A service that allows you to make a URL to a given entity with a single method call,
 * and not needing to know the implementation details behind the routing (route names, route
 * parameters, etc).
 *
 * It exposes these methods (and there is a Twig Extension that makes them avail. in templates)
 *
 * - getPortalPath(object, type=null, extra_params=array)
 * - getPortalUrl(object, type=null, extra_params=array)
 * - getAgentPath(object, type=null, extra_params=array)
 * - getAgentUrl(object, type=null, extra_params=array)
 */
class ObjectRouter
{
    /**
     * The string for the "portal" context (mostly used internally).
     */
    const CONTEXT_PORTAL = 'portal';

    /**
     * The string for the "agent" context (mostly used internally).
     */
    const CONTEXT_AGENT = 'agent';

    /**
     * The LinkConfigRepoInterface returns this value if the object's URL algorithm is
     * special/built-in to the getCustomUrl() method  (mostly used internally by default generators and config).
     */
    const CONFIG_CUSTOM = 'custom';

    /**
     * View the AppBundle's service resource "object_router.yml" to see how link generators are made and injected
     * ORDER MATTERS, first come first server when it comes to ->supports().
     *
     * @var LinkGeneratorInterface[]
     */
    private $link_generators;

    private $isDebug = false;

    /**
     * Constructor.
     *
     * @param array $link_generators
     * @param bool  $isDebug
     */
    public function __construct(array $link_generators, $isDebug)
    {
        $this->link_generators = $link_generators;
        $this->isDebug         = $isDebug;
    }

    /**
     * @param mixed  $object
     * @param string $type
     * @param array  $extra_params
     *
     * @return string
     */
    public function getPortalPath($object, $type = null, array $extra_params = [])
    {
        return $this->processLink(
            $object,
            $type,
            self::CONTEXT_PORTAL,
            UrlGeneratorInterface::ABSOLUTE_PATH,
            $extra_params
        );
    }

    /**
     * @param mixed  $object
     * @param string $type
     * @param array  $extra_params
     *
     * @return string
     */
    public function getPortalUrl($object, $type = null, array $extra_params = [])
    {
        return $this->processLink(
            $object,
            $type,
            self::CONTEXT_PORTAL,
            UrlGeneratorInterface::ABSOLUTE_URL,
            $extra_params
        );
    }

    /**
     * @param mixed  $object
     * @param string $type
     * @param array  $extra_params
     *
     * @return string
     */
    public function getAgentPath($object, $type = null, array $extra_params = [])
    {
        return $this->processLink(
            $object,
            $type,
            self::CONTEXT_AGENT,
            UrlGeneratorInterface::ABSOLUTE_PATH,
            $extra_params
        );
    }

    /**
     * @param mixed  $object
     * @param string $type
     * @param array  $extra_params
     *
     * @return string
     */
    public function getAgentUrl($object, $type = null, array $extra_params = [])
    {
        return $this->processLink(
            $object,
            $type,
            self::CONTEXT_AGENT,
            UrlGeneratorInterface::ABSOLUTE_URL,
            $extra_params
        );
    }

    /**
     * @param mixed  $object
     * @param string $type
     * @param string $context
     * @param string $reference_type
     * @param array  $extra_params
     *
     * @return string
     */
    protected function processLink($object, $type, $context, $reference_type, array $extra_params = [])
    {
        if (!is_object($object) && $this->isDebug) {
            $this->routerException($object, $type, $context);
        } elseif (!is_object($object)) {
            return '';
        }

        foreach ($this->link_generators as $link_generator) {
            if ($link_generator->supports($object, $type, $context)) {
                return $link_generator->generate($object, $type, $context, $extra_params, $reference_type);
            }
        }

        $this->routerException($object, $type, $context);

        return '';
    }

    protected function routerException($object, $type, $context)
    {
        throw new ObjectRouterException(
            sprintf(
                'could not generate a link for "%s" (type=%s) in context "%s" - no LinkGeneratorInterface that supports it',
                is_object($object) ? get_class($object) : 'non object',
                $type ?: 'no type',
                $context
            )
        );
    }
}
