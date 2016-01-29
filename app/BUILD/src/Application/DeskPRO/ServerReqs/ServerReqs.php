<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace Application\DeskPRO\ServerReqs;

use Doctrine\ORM\EntityManager;

class ServerReqs
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /** @var array */
    protected $web_checks = array();
    /** @var array */
    protected $cli_checks = array();

    /**
     * @var \Application\InstallBundle\Install\ServerChecks
     */
    protected $server_check;

    /**
     * @var array
     */
    protected $checksTable = array();

    public function __construct(EntityManager $em)
    {
    }

    /**
     * @return array
     */
    public function getWebChecks()
    {
        return $this->web_checks;
    }

    /**
     * @return array
     */
    public function getCliChecks()
    {
        return $this->cli_checks;
    }

    /**
     * @param array $errors
     * @param bool  $includeOptionals
     *
     * @return array
     */
    protected function _generateMessages(array $errors, $includeOptionals = false)
    {
        return [];
    }

    /**
     * @param bool $includeOptionals
     */
    protected function _generateCheckTable($includeOptionals = false)
    {
    }

    /**
     * optional extensions.
     */
    protected function addOptionals()
    {
    }
}
