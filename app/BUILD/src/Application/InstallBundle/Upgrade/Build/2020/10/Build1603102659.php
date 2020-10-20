<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1603102659 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $connection = $this->getDbConnection('default');

        $connection->update(
            'languages',
            [
                'base_filepath' => '%DP_ROOT%/locales/et-EE',
                'locale'        => 'et-EE',
            ],
            ['locale'   => 'et']
        );
        $connection->update(
            'languages',
            [
                'base_filepath' => '%DP_ROOT%/locales/hr-HR',
                'locale'        => 'hr-HR',
            ],
            ['locale'   => 'hr']
        );
        $connection->update(
            'languages',
            [
                'base_filepath' => '%DP_ROOT%/locales/lt-LT',
                'locale'        => 'lt-LT',
            ],
            ['locale'   => 'lt']
        );
        $connection->update(
            'languages',
            [
                'base_filepath' => '%DP_ROOT%/locales/lv-LV',
                'locale'        => 'lv-LV',
            ],
            ['locale'   => 'lv']
        );
        $connection->update(
            'languages',
            [
                'base_filepath' => '%DP_ROOT%/locales/ro-RO',
                'locale'        => 'ro-RO',
            ],
            ['locale'   => 'ro']
        );
    }
}
