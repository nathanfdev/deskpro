<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1582547774 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQueryQuiet('default', 'ALTER TABLE brands DROP FOREIGN KEY FK_7EA24434C0C33964');
        $this->execDbQueryQuiet('default', 'ALTER TABLE brands DROP FOREIGN KEY FK_7EA24434F1B7F8A2');
        $this->execDbQueryQuiet('default', 'ALTER TABLE brands ADD CONSTRAINT FK_7EA24434C0C33964 FOREIGN KEY (theme_set_id) REFERENCES theme_sets (id) ON DELETE SET NULL');
        $this->execDbQueryQuiet('default', 'ALTER TABLE brands ADD CONSTRAINT FK_7EA24434F1B7F8A2 FOREIGN KEY (edit_theme_set_id) REFERENCES theme_sets (id) ON DELETE SET NULL');
        $this->execDbQueryQuiet('default', 'ALTER TABLE theme_sets ADD title VARCHAR(255) DEFAULT NULL');
        $this->execDbQueryQuiet('default', 'ALTER TABLE theme_sets ADD brand_id INT DEFAULT NULL');
        $this->execDbQueryQuiet('default', 'ALTER TABLE theme_sets ADD CONSTRAINT FK_DE4AB1EC44F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE CASCADE');
        $this->execDbQueryQuiet('default', 'CREATE INDEX IDX_DE4AB1EC44F5D008 ON theme_sets (brand_id)');
        $this->execDbQueryQuiet('default', 'ALTER TABLE templates DROP FOREIGN KEY FK_6F287D8EC0C33964');
        $this->execDbQueryQuiet('default', 'ALTER TABLE templates ADD CONSTRAINT FK_6F287D8EC0C33964 FOREIGN KEY (theme_set_id) REFERENCES theme_sets (id) ON DELETE CASCADE');
    }

    public function run()
    {
        $connection = $this->getDbConnection('default');

        $brands = $connection->fetchAll('SELECT * FROM brands');
        foreach ($brands as $brand) {
            if ($brand['theme_set_id']) {
                $connection->update(
                    'theme_sets',
                    ['brand_id' => $brand['id']],
                    ['id'       => $brand['theme_set_id']]
                );
            }
            if ($brand['edit_theme_set_id']) {
                $connection->update(
                    'theme_sets',
                    ['brand_id' => $brand['id']],
                    ['id'       => $brand['edit_theme_set_id']]
                );
            }
        }
    }
}
