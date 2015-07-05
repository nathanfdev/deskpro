<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DpTest;

use DeskPRO\Kernel\ApiKernel;
use DeskPRO\Kernel\PortalKernel;

abstract class AbstractKernelAwareTestCase extends DeskProTestCase
{
    protected static $api_kernel;
    protected static $portal_kernel;
    protected static $last_installed_data_set;
    
    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected abstract function getContainer();
    
    /**
     * @param mixed $service
     * @return object
     */
    protected function get($service)
    {
        return $this->getContainer()->get($service);
    }

    /**
     * @param array $server - lets you override $_SERVER variables
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    public function getClient($server = array())
    {
        // TODO: we need a good way of setting the 'HTTP_HOST' key on $server to the dev's machine
        // maybe just use the a global we declare in config.test.php ?? might be best option.
        $client = $this->getContainer()->get('test.client');
        $client->setServerParameters($server);

        return $client;
    }

    /**
     * @param bool $force_reboot
     * @return ApiKernel
     */
    protected function getApiKernel($force_reboot = false)
    {
        if (self::$api_kernel && !$force_reboot) {
            return self::$api_kernel;
        }

        require_once DP_ROOT . '/sys/Kernel/ApiKernel.php';
        $kernel = new ApiKernel('test', true);
        $kernel->boot();

        self::$api_kernel = $kernel;

        return self::$api_kernel;
    }

    /**
     * @param bool $force_reboot
     * @return PortalKernel
     */
    protected function getPortalKernel($force_reboot = false)
    {
        if (self::$portal_kernel && !$force_reboot) {
            return self::$portal_kernel;
        }

        require_once DP_ROOT . '/sys/Kernel/PortalKernel.php';
        $kernel = new PortalKernel('test', true);
        $kernel->boot();

        self::$portal_kernel = $kernel;

        return self::$portal_kernel;
    }

    /**
     * Install a data set. To ensure a reinstall, send a flag.
     *
     * By default, if the data set you want is already installed before,
     * then nothing happens.
     *
     * @param string $data_set_id
     * @param bool $reinstall
     */
    public function installDataSet($data_set_id, $reinstall = false)
    {
        if (self::$last_installed_data_set === $data_set_id) {
            // the same data set is already loaded
            if (!$reinstall) {
                // no indication to reinstall, exit
                return;
            }
        }

        $this->get('dataset_manager')->install($data_set_id);

        // remember that we installed this
        self::$last_installed_data_set = $data_set_id;
    }

    /**
     * @param $entity
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getRepo($entity)
    {
        return $this->get('doctrine.orm.default_entity_manager')->getRepository($entity);
    }
}
