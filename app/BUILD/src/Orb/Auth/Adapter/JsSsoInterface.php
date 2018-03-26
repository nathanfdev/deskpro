<?php

/**
 * Orb.
 *
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
     * @param \Application\DeskPRO\Entity\Usersource                  $source
     * @param \Application\DeskPRO\Twig\Extension\TemplatingExtension $extension
     * @param \Application\DeskPRO\Entity\Person                      $person
     * @param                                                         $is_first_page
     *
     * @return mixed
     *
     * @deprecated requiring an auth app to render HTML should be avoided going forward
     */
    public function getSsoHtmlLoaderOutput(
        \Application\DeskPRO\Entity\Usersource $source,
        \Application\DeskPRO\Twig\Extension\TemplatingExtension $extension,
        \Application\DeskPRO\Entity\Person $person,
        $is_first_page
    );
}
