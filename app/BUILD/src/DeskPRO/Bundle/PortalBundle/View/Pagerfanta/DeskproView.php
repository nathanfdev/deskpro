<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\View\Pagerfanta;

use Pagerfanta\View\DefaultView;

class DeskproView extends DefaultView
{
    /**
     * @return Template\DeskproTemplate
     */
    protected function createDefaultTemplate()
    {
        return new Template\DeskproTemplate();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultProximity()
    {
        return 3;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'twitter_bootstrap';
    }
}
