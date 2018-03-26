<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Features;

use Application\DeskPRO\Entity;
use Application\DeskPRO\EntityRepository\Setting as SettingsRepository;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Features\AgentChatFeature;
use DeskPRO\Bundle\AppBundle\Features\BetaFeatureInterface;
use DpTest\ApiTestCase;

class AgentChatFeatureTest extends ApiTestCase
{
    /**
     * @var SettingsResolver
     */
    protected $settingsResolver;

    /**
     * @var AgentChatFeature
     */
    protected $feature;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function setUp()
    {
        $this->feature          = $this->getContainer()->get('deskpro.features.agent_chat');
        $this->settingsResolver = $this->getContainer()->get('settings_resolver');

        // clear db
        $em = $this->getEntityManager();
        $em->getConnection()->executeQuery("DELETE FROM `settings` WHERE `name` = '{$this->getKey()}'");
        $em->getConnection()->executeQuery('DELETE FROM tmp_data');
        $em->clear();
    }

    public function test_enable()
    {
        // todo add before enable specific test
        $this->feature->beforeEnable($this->getContainer());
    }

    public function test_disable()
    {
        // todo add before disable specific test
        $this->feature->beforeDisable($this->getContainer());
    }

    public function test_is_enabled_or_disabled()
    {
        $this->setUpSetting(true);
        $this->settingsResolver->getGlobalSettings(true);
        $this->assertTrue($this->feature->isEnabled());

        $this->setUpSetting(false);
        $this->settingsResolver->getGlobalSettings(true);
        $this->assertFalse($this->feature->isEnabled());
    }

    private function setUpSetting($enabled)
    {
        $key = $this->getKey();
        $em  = $this->getEntityManager();

        /** @var SettingsRepository $settingsRepo */
        $settingsRepo = $em->getRepository(Entity\Setting::class);
        $settingsRepo->updateSetting($key, $enabled);
    }

    /**
     * @return string
     */
    private function getKey()
    {
        return sprintf('%s.%s', BetaFeatureInterface::BETA_FEATURES_KEY, $this->feature->getId());
    }
}
