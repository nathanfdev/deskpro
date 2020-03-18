<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1582547777 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQueryQuiet('default', 'ALTER TABLE guides ADD icon_property_id INT DEFAULT NULL, ADD splash_image_property_id INT DEFAULT NULL, ADD color VARCHAR(6) DEFAULT NULL');
        $this->execDbQueryQuiet('default', 'ALTER TABLE guides ADD CONSTRAINT FK_4D7795EF4646DDA FOREIGN KEY (icon_property_id) REFERENCES icon_property (id) ON DELETE SET NULL');
        $this->execDbQueryQuiet('default', 'ALTER TABLE guides ADD CONSTRAINT FK_4D7795EF339FD429 FOREIGN KEY (splash_image_property_id) REFERENCES splash_image_property (id) ON DELETE SET NULL');
        $this->execDbQueryQuiet('default', 'CREATE INDEX IDX_4D7795EF4646DDA ON guides (icon_property_id)');
        $this->execDbQueryQuiet('default', 'CREATE INDEX IDX_4D7795EF339FD429 ON guides (splash_image_property_id)');
    }

    public function run()
    {
    }
}
