<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Annotation;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;

/**
 * A controller annotation. If present, all GET requests are immediately returned with a blank page and a JS
 * auto-submitting "Continue" button. Many routes (esp. in portal for subscriptions/ratings) are meant to be
 * POST only routes. However, we need to link to them. Normally JS on the origin page will turn that href into
 * a POST via events, but if JS is disabled or something happens, we still want a GET request to work via POSTing.
 * This prevents bots etc from accidentially rating and subscribing.
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class AutoPostOnGetRequest implements ConfigurationInterface
{
    const ALIAS                 = 'auto_post_on_get_request';
    const ALIAS_WITH_UNDERSCORE = '_auto_post_on_get_request';

    /**
     * {@inheritdoc}
     */
    public function getAliasName()
    {
        return self::ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    public function allowArray()
    {
        return false;
    }
}
