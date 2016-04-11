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

namespace DeskPRO\Bundle\PortalBundle\Theme;

use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use DpSys\LowError\SystemErrorHandler;

/**
 * Class TagProcessor.
 */
class TagProcessor
{
    /**
     * @var TagRequestFactory
     */
    private $tag_request_factory;

    /**
     * @var TagHandlerInterface[]
     */
    private $tag_handlers;

    /**
     * Constructor.
     *
     * @param TagRequestFactory $tag_request_factory
     * @param array             $tag_handlers
     */
    public function __construct(TagRequestFactory $tag_request_factory, array $tag_handlers)
    {
        $this->tag_request_factory = $tag_request_factory;
        $this->tag_handlers        = $tag_handlers;
    }

    /**
     * @param Tag   $tag
     * @param array $arguments
     *
     * @return string
     */
    public function process(Tag $tag, array $arguments = [])
    {
        $tag_request = $this->tag_request_factory->create($tag, $arguments);

        if (!$handler = $this->findHandler($tag, $tag_request)) {
            throw new \RuntimeException('no handler found for "'.$tag->getName().'"');
        }

        $response = $handler->handle($tag, $tag_request);

        if (!$response) {
            return ''; // be passive and default to blank
        } elseif (!$response->isSuccessful()) {
            if (!defined('DP_INTERFACE') || DP_INTERFACE !== 'test') {
                $e = new \RuntimeException('Unable to render theme content: '.$response->getContent());
                SystemErrorHandler::logException($e);
            }

            return ''; // be passive and default to blank
        }

        return $response->getContent();
    }

    /**
     * @param Tag        $tag
     * @param TagRequest $tag_request
     *
     * @return TagHandlerInterface
     */
    private function findHandler(Tag $tag, TagRequest $tag_request)
    {
        foreach ($this->tag_handlers as $handler) {
            if ($handler->supports($tag, $tag_request)) {
                return $handler;
            }
        }
    }
}
