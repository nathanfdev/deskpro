<?php

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
