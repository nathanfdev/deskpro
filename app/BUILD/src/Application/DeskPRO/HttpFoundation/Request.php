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

namespace Application\DeskPRO\HttpFoundation;

use Orb\Util\Strings;

/**
 * @deprecated Legacy code. Prefer using default Symfony request.
 */
class Request extends \Symfony\Component\HttpFoundation\Request
{
    const PARTIAL_REQUEST_KEY = '_partial';

    /**
     * When a client sends _partial in POST/GET data, they're requesting a partial result.
     *
     * For example: more search results, or a page being put into an existing page etc. The actual
     * meaning of what "partial" is depends on the page.
     *
     * Returns either 'partial', or a string value of the _partial (which might be used to denote different
     * types of partial templates).
     *
     * @return bool|string
     */
    public function isPartialRequest()
    {
        $val = false;

        if ($this->query->has(self::PARTIAL_REQUEST_KEY)) {
            $val = $this->query->get(self::PARTIAL_REQUEST_KEY);
            if (!$val) {
                $val = 'partial';
            }
        } elseif ($this->request->has(self::PARTIAL_REQUEST_KEY)) {
            $val = $this->request->get(self::PARTIAL_REQUEST_KEY);
            if (!$val) {
                $val = 'partial';
            }
        }

        return $val;
    }

    /**
     * returns bool only.
     *
     * @return bool
     */
    public function isPartial()
    {
        return !empty($_REQUEST[self::PARTIAL_REQUEST_KEY]);
    }

    public function isPost()
    {
        return $this->getMethod() == 'POST';
    }

    public function isGet()
    {
        return $this->getMethod() == 'GET';
    }

    public function getReturnParam()
    {
        if ('application/json' === $this->getContentType()) {
            if ($data = json_decode((string) $this->getContent(), 1)) {
                if (!$return = @$data['return']) {
                    return;
                }
            }
        }

        $return = $this->get('return');
        if (!$return || !is_string($return)) {
            return;
        }

        $return = Strings::removeInvisibleCharacters($return);
        if (!$return) {
            return;
        }

        if ('/' !== $return[0] || '//' === substr($return, 0, 2) || false !== strpos($return, '/validate-email/')) {
            return;
        }

        return $return;
    }
}
