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
namespace DeskPRO\Bundle\AppBundle\Translator;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * The "Validator" componenent of Symfony uses an "IdentityTranslator" by default and we don't want that.
 *
 * Instead, we use this noop translator, which does nothing whatsoever.
 *
 * Our own DeskPRO Translate object later does the real translating.
 */
class NoopTranslator implements TranslatorInterface
{
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        return $id;
    }

    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        return $id;
    }

    public function setLocale($locale)
    {
    }

    public function getLocale()
    {
    }
}
