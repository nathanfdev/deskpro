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
                'sys_name'          => 'dev_blankout',
                'title'             => 'Dev Blank Out',
                'base_filepath'     => '%DP_ROOT%/locales/dev_blankout',
                'locale'            => 'en-T1',
                'flag_image'        => 'locale_en-US.png',
                'is_rtl'            => 0,
                'has_user'          => 1,
                'has_agent'         => 1,
                'has_admin'         => 1,
                'plural_categories' => 'one,other',
                'plural_formula'    => 'n != 1',
            ],
            [
                'sys_name'          => 'dev_longstring',
                'title'             => 'Dev Long String',
                'base_filepath'     => '%DP_ROOT%/locales/dev_longstring',
                'locale'            => 'en-T2',
                'flag_image'        => 'locale_en-US.png',
                'is_rtl'            => 0,
                'has_user'          => 1,
                'has_agent'         => 1,
                'has_admin'         => 1,
                'plural_categories' => 'one,other',
                'plural_formula'    => 'n != 1',
            ],
            [
                'sys_name'          => 'dev_rtl',
                'title'             => 'Dev RTL',
                'base_filepath'     => '%DP_ROOT%/locales/dev_rtl',
                'locale'            => 'en-T3',
                'flag_image'        => 'locale_en-US.png',
                'is_rtl'            => 1,
                'has_user'          => 1,
                'has_agent'         => 1,
                'has_admin'         => 1,
                'plural_categories' => 'one,other',
                'plural_formula'    => 'n != 1',
            ],
            [
                'sys_name'          => 'arabic',
                'title'             => 'العربية',
                'base_filepath'     => '%DP_ROOT%/locales/ar',
                'locale'            => 'ar',
                'flag_image'        => 'locale_ar.png',
                'is_rtl'            => 1,
                'has_user'          => 1,
                'has_agent'         => 1,
                'has_admin'         => 1,
                'plural_categories' => 'zero,one,two,few,many,other',
                'plural_formula'    => '(n == 0) ? 0 : ((n == 1) ? 1 : ((n == 2) ? 2 : ((n % 100 >= 3 && n % 100 <= 10) ? 3 : ((n % 100 >= 11 && n % 100 <= 99) ? 4 : 5))))',
            ],
            [
                'sys_name'          => 'french',
                'title'             => 'Français',
                'base_filepath'     => '%DP_ROOT%/locales/fr',
                'locale'            => 'fr',
                'flag_image'        => 'locale_fr.png',
                'is_rtl'            => 0,
                'has_user'          => 1,
                'has_agent'         => 1,
                'has_admin'         => 1,
                'plural_categories' => 'one,other',
                'plural_formula'    => 'n > 1',
            ],
        ];

        $this->db->batchInsert('languages', $batch);
    }
}
