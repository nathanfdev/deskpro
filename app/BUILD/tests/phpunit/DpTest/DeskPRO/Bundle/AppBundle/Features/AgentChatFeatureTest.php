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
