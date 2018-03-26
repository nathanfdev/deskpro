<?php

/**
 * DeskPRO.
 */

namespace DpBehat\Portal;

use DpBehat\BaseContext;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAware;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Factory as PageObjectFactory;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

abstract class BasePortalContext extends BaseContext implements PageObjectAware
{
    /**
     * @var PageObjectFactory
     */
    private $pageObjectFactory = null;

    /**
     * @param string $name
     *
     * @throws \RuntimeException
     *
     * @return Page
     */
    public function getPage($name)
    {
        if (null === $this->pageObjectFactory) {
            throw new \RuntimeException('To create pages you need to pass a factory with setPageObjectFactory()');
        }

        return $this->pageObjectFactory->createPage($name);
    }

    /**
     * @param string $name
     *
     * @throws \RuntimeException
     *
     * @return Element
     */
    public function getElement($name)
    {
        if (null === $this->pageObjectFactory) {
            throw new \RuntimeException('To create elements you need to pass a factory with setPageObjectFactory()');
        }

        return $this->pageObjectFactory->createElement($name);
    }

    /**
     * {@inheritdoc}
     */
    public function setPageObjectFactory(PageObjectFactory $pageObjectFactory)
    {
        $this->pageObjectFactory = $pageObjectFactory;
    }

    /**
     * @return PageObjectFactory
     */
    public function getPageObjectFactory()
    {
        if (null === $this->pageObjectFactory) {
            throw new \RuntimeException('To access the page factory you need to pass it first with setPageObjectFactory()');
        }

        return $this->pageObjectFactory;
    }

    /**
     * @param $phrase
     *
     * @return string|null
     */
    protected function phrase($phrase)
    {
        return $this->get('language_manager')->phrase($phrase);
    }
}
