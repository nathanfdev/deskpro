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
 */

namespace DeskPRO\Bundle\AppBundle\ObjectRouter;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ObjectRouter
{
    /**
     * The string for the "portal" context (mostly used internally)
     */
    const CONTEXT_PORTAL = 'portal';

    /**
     * The string for the "agent" context (mostly used internally)
     */
    const CONTEXT_AGENT = 'agent';

    /**
     * The ObjectRouterConfigInterface returns this value if the object's URL algorithm is
     * special/built-in to the getCustomUrl() method  (mostly used internally)
     */
    const SIGNAL_CUSTOM = 'custom';

    /**
     * @var UrlGeneratorInterface
     */
    private $url_generator;

    /**
     * @var SettingsResolver
     */
    private $settings_resolver;

    /**
     * @var ObjectRouterConfigInterface
     */
    private $config;

    public function __construct(UrlGeneratorInterface $url_generator, SettingsResolver $settings_resolver, ObjectRouterConfigInterface $config)
    {
        $this->url_generator = $url_generator;
        $this->settings_resolver = $settings_resolver;
        $this->config = $config;
    }

    public function getPortalPath($object, $type = null)
    {
        $config = $this->getConfig($object, self::CONTEXT_PORTAL, $type);

        if (self::SIGNAL_CUSTOM === $config) {
            return $this->getCustomUrl(
                $object,
                self::CONTEXT_PORTAL,
                $type,
                UrlGeneratorInterface::ABSOLUTE_PATH
            );
        }

        return $this->generateStandard(
            $config['route'],
            $config['route_params'],
            UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }

    /**
     * @param object $object
     * @param string|null $type
     * @return string
     */
    public function getPortalUrl($object, $type = null)
    {
        $config = $this->getConfig($object, self::CONTEXT_PORTAL, $type);


        if (self::SIGNAL_CUSTOM === $config) {
            return $this->getCustomUrl(
                $object,
                self::CONTEXT_PORTAL,
                $type,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return $this->generateStandard(
            $config['route'],
            $config['route_params'],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function getAgentPath($object, $type = null)
    {
        $config = $this->getConfig($object, self::CONTEXT_AGENT, $type);

        if (self::SIGNAL_CUSTOM === $config) {
            return $this->getCustomUrl(
                $object,
                self::CONTEXT_AGENT,
                $type,
                UrlGeneratorInterface::ABSOLUTE_PATH
            );
        }

        return $this->generateStandard(
            $config['route'],
            $config['route_params'],
            UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }

    public function getAgentUrl($object, $type = null)
    {
        $config = $this->getConfig($object, self::CONTEXT_AGENT, $type);

        if (self::SIGNAL_CUSTOM === $config) {
            return $this->getCustomUrl(
                $object,
                self::CONTEXT_AGENT,
                $type,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return $this->generateStandard(
            $config['route'],
            $config['route_params'],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    protected function getCustomUrl($object, $context, $type = null, $reference_type)
    {
        if ($object instanceof Ticket) {
            return $this->generateForTicket($object, $context, $type, $reference_type);
        }

        throw $this->createException('no custom URL generator', $object, $type, $context);
    }

    protected function generateForTicket(Ticket $ticket, $context, $type, $reference_type)
    {
        if ($this->getSetting('core.tickets.use_ref')) {
            $ref = $ticket->getRef();
        } else {
            $ref = $ticket->getId();
        }

        return $this->generateStandard(
            'portal_tickets_view',
            array('ticket_ref' => $ref),
            $reference_type
        );
    }

    protected function getSetting($name, $default = null)
    {
        return $this->settings_resolver->getGlobalSettings()->get($name, $default);
    }

    /**
     * Standard internal generation of a route
     *
     * @param $route_name
     * @param array $route_params
     * @param $reference_type
     * @return string
     */
    protected function generateStandard($route_name, array $route_params, $reference_type)
    {
        return $this->url_generator->generate($route_name, $route_params, $reference_type);
    }

    /**
     * @param $object
     * @param $context
     * @param $type
     * @return array|string
     */
    protected function getConfig($object, $context, $type)
    {
        if (!$config = $this->config->getRouteInfo($object, $context, $type)) {
            throw $this->createException('cannot find ObjectRouter config', $object, $type, $context);
        }

        return $config;
    }

    protected function createException($msg, $object, $type, $context)
    {
        return new ObjectRouterException(
            'ObjectRouter - '
            . $msg
            . sprintf(
                '[for "%s" with type "%s" in context "%s"]',
                is_object($object) ? get_class($object) : 'scalar',
                $type ?: 'default',
                $context
            )
        );
    }
}
