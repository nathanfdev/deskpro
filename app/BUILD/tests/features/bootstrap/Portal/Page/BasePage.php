<?php

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
    public function __construct(Session $session, Factory $factory, array $parameters = [])
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
    protected function getUrl(array $urlParameters = [])
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

    protected function verifyUrl(array $urlParameters = [])
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
