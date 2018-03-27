<?php

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
