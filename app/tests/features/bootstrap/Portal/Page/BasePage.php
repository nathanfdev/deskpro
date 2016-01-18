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
namespace DpBehat\Portal\Page;

use Behat\Mink\Session;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\UnexpectedPageException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Factory;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class BasePage extends Page
{
    /**
     * @param Session $session
     * @param Factory $factory
     * @param array   $parameters
     */
    public function __construct(Session $session, Factory $factory, array $parameters = array())
    {
        parent::__construct($session, $factory, $parameters);
        // ensure no base_url is used. this test suite is meant to be run in the symfony kernel only
        // to support phantomjs or any external driver, you'll need to do some work (I don't know what, though)
        if (isset($parameters['base_url'])) {
            $parameters['base_url'] = null;
        }
    }

    /**
     * @param array $urlParameters
     *
     * @return string
     */
    protected function getUrl(array $urlParameters = array())
    {
        return $this->makeSurePathIsAbsolute($this->unmaskUrl($urlParameters));
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function makeSurePathIsAbsolute($path)
    {
        $baseUrl = '/';

        return 0 !== strpos($path, '/') ? $baseUrl.ltrim($path, '/') : $path;
    }

    /**
     * @param array $urlParameters
     *
     * @return string
     */
    private function unmaskUrl(array $urlParameters)
    {
        $url = $this->getPath();

        foreach ($urlParameters as $parameter => $value) {
            $url = str_replace(sprintf('{%s}', $parameter), $value, $url);
        }

        return $url;
    }

    protected function verifyUrl(array $urlParameters = array())
    {
        // we need to override this to allow for not using a hostname at all in the session (it uses localhost)

        if ($this->removeHostAndScheme($this->getSession()->getCurrentUrl()) === $this->getUrl($urlParameters)) {
            return;
        }

        // override to allow language prefixes in URLs
        if (strpos($this->getSession()->getCurrentUrl(), $this->getUrl($urlParameters)) === false) {
            throw new UnexpectedPageException(sprintf(
                'Expected to be on "%s" but found "%s" instead',
                $this->getUrl($urlParameters), $this->getSession()->getCurrentUrl()
            ));
        }
    }

    private function removeHostAndScheme($url)
    {
        $url_info = parse_url($url);

        $uri = $url_info['path'];

        if (isset($url_info['query'])) {
            $uri .= '?'.$url_info['query'];
        }

        return $uri;
    }
}
