<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\InstallBundle\Upgrade\Build;

use Symfony\Component\Filesystem\Filesystem;

class Build1451313483 extends AbstractBuild
{
    public function run()
    {
        $this->execMutateSql('delete from app_instances where package_name = "deskpro_us_google"');
        $this->execMutateSql('delete from app_assets where package_name = "deskpro_us_google"');
        $this->execMutateSql('delete from app_packages where name = "deskpro_us_google"');

        try {
            $fs = new Filesystem();
            $fs->remove(DP_WEB_ROOT.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.'deskpro_us_google');
        } catch (\Exception $e) {
            $this->out('Unlink failed: '.$e->getMessage());
        }
    }
}
