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
 * Created by PhpStorm.
 * User: julien
 * Date: 11/04/2016
 * Time: 10:20.
 */

namespace Application\DeskPRO\Twig\Extension;

use Application\DeskPRO\App;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class UserDateExtension extends \Twig_Extension
{
    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected $container;
    /** @var array */
    protected $counter_registry;

    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return \Application\DeskPRO\Templating\Engine
     */
    public function getTemplating()
    {
        return $this->container->get('templating');
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'deskpro_userdate';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            'date' => new \Twig_Filter_Method($this, 'userDate', ['needs_context' => true]),
        ];
    }

    public function userDate($context, $date, $format = 'fulltime', $timezone = null)
    {
        // Backwards compat calls: args shifted back one
        if (!is_array($context)) {
            $args = func_get_args();
            if (!isset($args[1])) {
                $args[1] = 'F j, Y H:i';
            }
            if (!isset($args[2])) {
                $args[2] = null;
            }

            list($date, $format, $timezone) = $args;
            $context                        = null;
        }

        switch ($format) {
            case 'full':
                //D, jS M Y
                $format = App::getSetting('core.date_full');
                break;

            case 'fulltime':
                //D, jS M Y g:ia
                $format = App::getSetting('core.date_fulltime');
                break;

            case 'day':
                //M j Y
                $format = App::getSetting('core.date_day');
                break;

            case 'day_short':
                //M j
                $format = App::getSetting('core.date_day_short');
                break;

            case 'time':
                //g:i a
                $format = App::getSetting('core.date_time');
                break;
        }

        if (!($date instanceof \DateTime)) {
            if (ctype_digit((string) $date)) {
                $date = new \DateTime('@'.$date);
                $date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            } else {
                try {
                    $date_str = $date;
                    $date     = new \DateTime($date_str);
                } catch (\Exception $e) {
                }
            }
        }

        if (!($date instanceof \DateTime)) {
            $date_str = (string) $date;

            return "invalid_date($date_str)";
        }

        if ($timezone === null && $context && isset($context['context']['person_timezone'])) {
            $timezone = $context['context']['person_timezone'];
        }

        if ($timezone === null && App::getCurrentPerson()) {
            $timezone = App::getCurrentPerson();
        }

        if ($timezone instanceof \Application\DeskPRO\Entity\Person) {
            $timezone = $timezone->getDateTimezone();
        }

        if (null !== $timezone) {
            if (!($timezone instanceof \DateTimeZone)) {
                $timezone = new \DateTimeZone($timezone);
            }
        }

        if (!$timezone || $timezone == 'UTC') {
            $timezone = new \DateTimeZone('UTC');
        }

        $date->setTimezone($timezone);

        $prefix = 'user.time.';
        if (DP_INTERFACE == 'admin' || DP_INTERFACE == 'agent') {
            $prefix = 'agent.time.';
        }

        return $this->container->getTranslator()->date($format, $date, $prefix);
    }
}
