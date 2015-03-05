<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * Orb
 *
 * @package Orb
 * @category Auth
 */

namespace Orb\Auth\Adapter;

/**
 * This forces an authentication class to render HTML, usually with Twig, and is therefore violating
 * several OOP principles.

 * This interface is used only by the Magento app, and I suggest we keep it that way. Please see
 * SsoCapableInterface and/or IFrameSsoInterface.
 *
 * @deprecated
 */
interface JsSsoInterface extends SsoLoginActionInterface
{
    /**
     * @param  \Application\DeskPRO\Entity\Usersource                  $source
     * @param  \Application\DeskPRO\Twig\Extension\TemplatingExtension $extension
     * @param  \Application\DeskPRO\Entity\Person                      $person
     * @param                                                          $is_first_page
     * @return mixed
     * @deprecated requiring an auth app to render HTML should be avoided going forward
     */
    public function getSsoHtmlLoaderOutput(
        \Application\DeskPRO\Entity\Usersource $source,
        \Application\DeskPRO\Twig\Extension\TemplatingExtension $extension,
        \Application\DeskPRO\Entity\Person $person,
        $is_first_page
    );
}
