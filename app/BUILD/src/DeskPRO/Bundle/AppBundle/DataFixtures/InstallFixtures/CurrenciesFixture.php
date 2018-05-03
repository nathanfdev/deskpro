<?php

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
            ->setSymbol('CHF')
        ;

        $manager->persist($currency);

        // CAD
        $currency = new Currency();
        $currency
            ->setName('Canadian dollar')
            ->setCurrencyCode('CAD')
            ->setSymbol('CA$')
        ;

        $manager->persist($currency);

        // HKD
        $currency = new Currency();
        $currency
            ->setName('Hong Kong dollar')
            ->setCurrencyCode('HKD')
            ->setSymbol('HK$')
        ;

        $manager->persist($currency);

        // SEK
        $currency = new Currency();
        $currency
            ->setName('Swedish krona')
            ->setCurrencyCode('SEK')
            ->setSymbol('SEK')
        ;

        $manager->persist($currency);

        // NZD
        $currency = new Currency();
        $currency
            ->setName('New Zealand dollar')
            ->setCurrencyCode('NZD')
            ->setSymbol('NZ$')
        ;

        $manager->persist($currency);

        $manager->flush();
    }
}
