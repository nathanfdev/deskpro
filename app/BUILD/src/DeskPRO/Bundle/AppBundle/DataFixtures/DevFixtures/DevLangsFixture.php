<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * This enables a few langs and the two dev langs.
 */
class DevLangsFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 30;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $batch = [
            [
                'sys_name'      => 'dev_blankout',
                'lang_code'     => 'eng',
                'title'         => 'Dev Blank Out',
                'base_filepath' => '%DP_ROOT%/languages/dev_blankout',
                'locale'        => 'en_T1',
                'flag_image'    => 'us.png',
                'is_rtl'        => 0,
                'has_user'      => 1,
                'has_agent'     => 1,
                'has_admin'     => 1,
            ],
            [
                'sys_name'      => 'dev_longstring',
                'lang_code'     => 'eng',
                'title'         => 'Dev Long String',
                'base_filepath' => '%DP_ROOT%/languages/dev_longstring',
                'locale'        => 'en_T2',
                'flag_image'    => 'us.png',
                'is_rtl'        => 0,
                'has_user'      => 1,
                'has_agent'     => 1,
                'has_admin'     => 1,
            ],
            [
                'sys_name'      => 'dev_rtl',
                'lang_code'     => 'eng',
                'title'         => 'Dev RTL',
                'base_filepath' => '%DP_ROOT%/languages/dev_rtl',
                'locale'        => 'en_T3',
                'flag_image'    => 'us.png',
                'is_rtl'        => 1,
                'has_user'      => 1,
                'has_agent'     => 1,
                'has_admin'     => 1,
            ],
            [
                'sys_name'      => 'arabic',
                'lang_code'     => 'ara',
                'title'         => 'العربية',
                'base_filepath' => '%DP_ROOT%/languages/arabic',
                'locale'        => 'ar',
                'flag_image'    => 'arabic.png',
                'is_rtl'        => 1,
                'has_user'      => 1,
                'has_agent'     => 0,
                'has_admin'     => 0,
            ],
            [
                'sys_name'      => 'french',
                'lang_code'     => 'fre',
                'title'         => 'Français',
                'base_filepath' => '%DP_ROOT%/languages/french',
                'locale'        => 'fr',
                'flag_image'    => 'fr.png',
                'is_rtl'        => 0,
                'has_user'      => 1,
                'has_agent'     => 1,
                'has_admin'     => 0,
            ],
        ];

        $this->db->batchInsert('languages', $batch);
    }
}
