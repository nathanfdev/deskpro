<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use DeskPRO\Bundle\AppBundle\Entity\Currency;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class CurrenciesFixture.
 */
class CurrenciesFixture extends AbstractDpFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        // USD
        $currency = new Currency();
        $currency
            ->setName('US Dollar')
            ->setCurrencyCode('USD')
            ->setSymbol('$')
        ;

        $manager->persist($currency);

        // GBP
        $currency = new Currency();
        $currency
            ->setName('British Pound ')
            ->setCurrencyCode('GBP')
            ->setSymbol('£')
        ;

        $manager->persist($currency);

        // EUR
        $currency = new Currency();
        $currency
            ->setName('Euro')
            ->setCurrencyCode('EUR')
            ->setSymbol('€')
        ;

        $manager->persist($currency);

        // JPY
        $currency = new Currency();
        $currency
            ->setName('Japanese yen')
            ->setCurrencyCode('JPY')
            ->setSymbol(' ¥‎')
        ;

        $manager->persist($currency);

        // AUD
        $currency = new Currency();
        $currency
            ->setName('Australian dollar')
            ->setCurrencyCode('AUD')
            ->setSymbol('A$')
        ;

        $manager->persist($currency);

        // CHF
        $currency = new Currency();
        $currency
            ->setName('Swiss franc')
            ->setCurrencyCode('CHF')
            ->setSymbol('SFr.')
        ;

        $manager->persist($currency);

        // CAD
        $currency = new Currency();
        $currency
            ->setName('Canadian dollar')
            ->setCurrencyCode('CAD')
            ->setSymbol('C$')
        ;

        $manager->persist($currency);

        // HKD
        $currency = new Currency();
        $currency
            ->setName('Hong Kong dollar')
            ->setCurrencyCode('HKD')
            ->setSymbol('hk$')
        ;

        $manager->persist($currency);

        // SEK
        $currency = new Currency();
        $currency
            ->setName('Swedish krona')
            ->setCurrencyCode('SEK')
            ->setSymbol('kr')
        ;

        $manager->persist($currency);

        // NZD
        $currency = new Currency();
        $currency
            ->setName('New Zealand dollar')
            ->setCurrencyCode('NZD')
            ->setSymbol('$')
        ;

        $manager->persist($currency);

        $manager->flush();
    }
}
