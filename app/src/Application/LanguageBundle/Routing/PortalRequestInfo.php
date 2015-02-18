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
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\LanguageBundle\Routing;

use Application\AppBundle\Helper\IsProxyRequestHelper;
use Application\PortalBundle\Mode\PortalMode;
use Symfony\Component\HttpFoundation\Request;

class PortalRequestInfo
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var PortalMode
     */
    private $mode;

    public function __construct(Request $request, PortalMode $mode = null)
    {
        $this->request = $request;
        $this->mode = $mode;
    }

    public function getLanguageUrlCode()
    {
        $pathinfo = $this->getReleventPathInfo();

        $matcher = new UrlMatcher();
        $info = $matcher->extractLanguageCode($pathinfo);

        return $info['lang_url_code'];
    }

    public function getRoutablePath()
    {
        $pathinfo = $this->getReleventPathInfo();

        $matcher = new UrlMatcher();
        $info = $matcher->extractLanguageCode($pathinfo);

        return $info['remaining_pathinfo'];
    }

    protected function getReleventPathInfo()
    {
        if ($this->mode) {
            return $this->mode->getInternalPath();
        }

        return $this->request->getPathInfo();
    }

    public function isSpecialPath()
    {
        if (IsProxyRequestHelper::check($this->request)) {
            return true;
        }

        $pathinfo = $this->request->getPathInfo();

        if ('/_' === substr($pathinfo, 0, 2)) {
            return true;
        }


        return false;
    }
}
