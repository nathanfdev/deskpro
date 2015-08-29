<?php

namespace DpFixtures\Import;

use Application\DeskPRO\Entity\Language;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class Languages
 * @package DpFixtures\Import
 */
class Languages extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $language = new Language();

        $language['title']      = 'Español';
        $language['locale']     = 'es_ES';
        $language['sys_name']   = 'spanish';
        $language['flag_image'] = 'es.png';
        $language['lang_code']  = 'spa';

        $manager->persist($language);
        $manager->flush();
    }
}
