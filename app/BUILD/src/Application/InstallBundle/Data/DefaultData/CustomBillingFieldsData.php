<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

/**
 * DeskPRO.
 *
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

class CustomBillingFieldsData extends AbstractDefaultData
{
    public function runInstall()
    {
        $this->getDb()->executeQuery("
            INSERT INTO `custom_def_billing`
				(`parent_id`, `app_id`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `handler_class`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `default_value`, `is_agent_field`)
			VALUES
				(NULL, NULL, '', 0, 0, 'Comment', '', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Text', 'a:0:{}', 1, 1, 0, NULL, 0);
		");
    }

    public function runInstallViaUpgrade()
    {
        // nothing because the upgrader installs the comment field as part of the upgrade step
    }

    public function runReset()
    {
        // nothing
    }

    public function runSync()
    {
        // nothing
    }
}
