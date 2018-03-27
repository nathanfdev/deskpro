<?php

namespace DpBehat\Api;

use Behat\Behat\Context\Context;
use DpBehat\BaseContext;
use DpBehat\Data\DataContext;
use DpBehat\RebootableContextInterface;

/**
 * Defines application features from the specific context.
 */
class ModesAndTagsContext extends BaseContext implements RebootableContextInterface
{
    public function rebootContext()
    {
        /* \DpRun\DpEnv $DP_ENV */
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
        $factory        = $this->get('api_authorization.action_permissions.metadata_factory');
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
        $em               = $this->get('doctrine.orm.default_entity_manager');
        $key_actions_repo = $em->getRepository('DeskPRO\Bundle\AppBundle\Entity\ApiKeyAction');
        $key_action       = $key_actions_repo->findOneBy(['key' => DataContext::getReference('apiKey')]);
        $key_action->setAction($tag);
        $this->persistAndFlush($key_action);
    }
}
