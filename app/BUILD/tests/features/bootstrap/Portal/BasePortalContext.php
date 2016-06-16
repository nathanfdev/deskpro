<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
