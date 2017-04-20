<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DpSys\SoftwareRequirements;

class PhpIniRequirement extends Requirement
{
    /**
     * Constructor that initializes the requirement.
     *
     * @param string        $cfgName           The configuration name used for ini_get()
     * @param bool|callback $evaluation        Either a boolean indicating whether the configuration should evaluate to true or false,
     *                                         or a callback function receiving the configuration value as parameter to determine the fulfillment of the requirement
     * @param bool          $approveCfgAbsence If true the Requirement will be fulfilled even if the configuration option does not exist, i.e. ini_get() returns false.
     *                                         This is helpful for abandoned configs in later PHP versions or configs of an optional extension, like Suhosin.
     *                                         Example: You require a config to be true but PHP later removes this config and defaults it to true internally
     * @param string|null   $testMessage       The message for testing the requirement (when null and $evaluation is a boolean a default message is derived)
     * @param string|null   $helpHtml          The help text formatted in HTML for resolving the problem (when null and $evaluation is a boolean a default help is derived)
     * @param string|null   $helpText          The help text (when null, it will be inferred from $helpHtml, i.e. stripped from HTML tags)
     * @param bool          $optional          Whether this is only an optional recommendation not a mandatory requirement
     */
    public function __construct($cfgName, $evaluation, $approveCfgAbsence = false, $testMessage = null, $helpHtml = null, $helpText = null, $optional = false)
    {
        $cfgValue = ini_get($cfgName);

        if (is_callable($evaluation)) {
            if (null === $testMessage || null === $helpHtml) {
                throw new \InvalidArgumentException('You must provide the parameters testMessage and helpHtml for a callback evaluation.');
            }

            $fulfilled = call_user_func($evaluation, $cfgValue);
        } else {
            if (null === $testMessage) {
                $testMessage = sprintf('%s %s be %s in php.ini',
                    $cfgName,
                    $optional ? 'should' : 'must',
                    $evaluation ? 'enabled' : 'disabled'
                );
            }

            if (null === $helpHtml) {
                $helpHtml = sprintf('Set <strong>%s</strong> to <strong>%s</strong> in php.ini<a href="#phpini">*</a>.',
                    $cfgName,
                    $evaluation ? 'on' : 'off'
                );
            }

            $fulfilled = $evaluation == $cfgValue;
        }

        parent::__construct($fulfilled || ($approveCfgAbsence && false === $cfgValue), $testMessage, $helpHtml, $helpText, $optional);
    }
}
