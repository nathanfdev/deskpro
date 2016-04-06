<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Bridge;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\PHP\ErrorEvent;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\EventLogger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SystemAlertsMonologHandler.
 */
class SystemAlertsMonologHandler extends AbstractProcessingHandler
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     * @param bool|int           $level
     * @param bool               $bubble
     */
    public function __construct(ContainerInterface $container, $level = Logger::NOTICE, $bubble = false)
    {
        $this->container = $container;
        parent::__construct($level, $bubble);
    }

    /**
     * @param array $record
     *
     * @throws \Exception
     */
    protected function write(array $record)
    {
        $context = array_key_exists('context', $record) ? $record['context'] : [];
        $this->getEventLogger()->log(new ErrorEvent(
            array_key_exists('code', $context)    ? $context['code']    : null,
            array_key_exists('message', $context) ? $context['message'] : null,
            array_key_exists('file', $context)    ? $context['file']    : null,
            array_key_exists('line', $context)    ? $context['line']    : null,
            null,
            $record
        ));
    }

    /**
     * @return EventLogger
     */
    private function getEventLogger()
    {
        return $this->container->get('dp_sys.alerts.event_logger');
    }
}
