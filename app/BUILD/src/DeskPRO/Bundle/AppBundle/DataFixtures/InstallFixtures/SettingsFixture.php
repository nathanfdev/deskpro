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
namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use Application\DeskPRO\Entity\Setting;
use DeskPRO\Component\Util\RandUtils;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class SettingsFixture extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 10;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach (array(
            'core.done_data_initializer' => 1,
            'core.deskpro_build' => defined('DP_BUILD_TIME') ? DP_BUILD_TIME : 0,
            'core.deskpro_build_num' => defined('DP_BUILD_NUM') ? DP_BUILD_NUM : 0,
            'core.install_build' => defined('DP_BUILD_TIME') ? DP_BUILD_TIME : time(),
            'core.install_timestamp' => time(),
            'core.install_key' => RandUtils::randomStringFormat('%25An'),
            'core.app_secret' => RandUtils::randomStringFormat('%75An'),
            'portal.widget.enabled' => 1,
            'core_tickets.use_ref' => 1,
        ) as $name => $value) {
            $s        = $this->findOrCreate($name, $manager);
            $s->value = $value;
            $manager->persist($s);
        }

        $manager->flush();
    }

    /**
     * @param string        $name
     * @param ObjectManager $manager
     *
     * @return \Application\DeskPRO\Entity\Setting
     */
    private function findOrCreate($name, ObjectManager $manager)
    {
        $setting = $manager->getRepository(Setting::class)->findBy(array('name' => $name));
        if (!$setting) {
            $setting       = new Setting();
            $setting->name = $name;
        }

        return $setting;
    }
}
