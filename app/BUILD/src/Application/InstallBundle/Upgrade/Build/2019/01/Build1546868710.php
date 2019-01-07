<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1546868710 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_ar.png' WHERE flag_image = 'arabic.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_cs.png' WHERE flag_image = 'cz.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_cy-GB.png' WHERE flag_image = 'cy.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_da.png' WHERE flag_image = 'dk.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_de.png' WHERE flag_image = 'de.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_en-GB.png' WHERE flag_image = 'gb.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_en-US.png' WHERE flag_image = 'us.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_es-ES.png' WHERE flag_image = 'es.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_fa.png' WHERE flag_image = 'ir.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_fi.png' WHERE flag_image = 'fi.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_fr.png' WHERE flag_image = 'fr.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_hu.png' WHERE flag_image = 'hu.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_id.png' WHERE flag_image = 'id.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_it.png' WHERE flag_image = 'it.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_ja.png' WHERE flag_image = 'jp.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_ko.png' WHERE flag_image = 'kr.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_nl.png' WHERE flag_image = 'nl.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_no.png' WHERE flag_image = 'no.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_pl.png' WHERE flag_image = 'pl.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_pt.png' WHERE flag_image = 'pt.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_ro.png' WHERE flag_image = 'ro.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_ru.png' WHERE flag_image = 'ru.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_sk.png' WHERE flag_image = 'sk.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_sl-SI.png' WHERE flag_image = 'si.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_sv.png' WHERE flag_image = 'se.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_tr.png' WHERE flag_image = 'tr.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_vi.png' WHERE flag_image = 'vn.png'");
        $this->execDbQuery('default', "UPDATE languages SET flag_image = 'locale_zh-CN.png' WHERE flag_image = 'cn.png'");
    }
}
