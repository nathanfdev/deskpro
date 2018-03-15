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

namespace Application\InstallBundle\Upgrade\Build;

class Build1521118759 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE currencies (id INT AUTO_INCREMENT NOT NULL, currency_code VARCHAR(50) NOT NULL, name VARCHAR(255) NOT NULL, symbol VARCHAR(50) NOT NULL, decimal_places INT NOT NULL, UNIQUE INDEX unique_currency_code (currency_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', <<<'SQL'
INSERT INTO `currencies` (`currency_code`, `name`, `symbol`, `decimal_places`) VALUES  
('USD', 'US Dollar', '$', '2'),
('GBP', 'British Pound ', '£', '2'),
('EUR', 'Euro', '€', '2'),
('JPY', 'Japanese yen', ' ¥‎', '2'),
('AUD', 'Australian dollar', 'A$', '2'),
('CHF', 'Swiss franc', 'SFr.', '2'),
('CAD', 'Canadian dollar', 'C$', '2'),
('HKD', 'Hong Kong dollar', 'hk$', '2'),
('SEK', 'Swedish krona', 'kr', '2'),
('NZD', 'New Zealand dollar', '$', '2')
SQL
        );
    }
}
