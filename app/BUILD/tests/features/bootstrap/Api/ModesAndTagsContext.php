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

namespace DpBehat\Api;

use Behat\Behat\Context\Context;
use DpBehat\BaseContext;
use DpBehat\RebootableContextInterface;

/**
 * Defines application features from the specific context.
 */
class ModesAndTagsContext extends BaseContext implements RebootableContextInterface
{
    public function rebootContext()
    {
        /** \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;
        $cache_dir = $DP_ENV->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.'api_permissions';
        file_exists($cache_dir) ? rmdir($cache_dir) : null;
    }

    /**
     * @Given I set apiModes to :mode for :controller_fqcn
     *
     * @param string $mode
     * @param string $controller_fqcn
     */
    public function setModeToController($mode, $controller_fqcn)
    {
        $factory        = $this->getContainer()->get('api_authorization.action_permissions.metadata_factory');
        $class_metadata = $factory->getMetadataForClass($controller_fqcn);
        foreach ($class_metadata->methodMetadata as $method) {
            $class_metadata->methodMetadata[$method->name]->setModes([$mode]);
        }
        $factory->loaded_metadata[$controller_fqcn] = $class_metadata;
    }

    /**
     * @Given I set tags for my key to :tag
     *
     * @param string $tag
     */
    public function setTagForMyKey($tag)
    {
        $em               = $this->getKernel()->getContainer()->get('doctrine.orm.default_entity_manager');
        $key_actions_repo = $em->getRepository('DeskPRO\Bundle\AppBundle\Entity\ApiKeyAction');
        $key_action       = $key_actions_repo->findOneBy(['key' => 1]);
        $key_action->setAction($tag);
        $this->persistAndFlush($key_action);
    }
}
