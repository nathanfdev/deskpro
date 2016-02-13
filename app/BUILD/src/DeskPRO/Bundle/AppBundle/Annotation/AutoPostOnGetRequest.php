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
