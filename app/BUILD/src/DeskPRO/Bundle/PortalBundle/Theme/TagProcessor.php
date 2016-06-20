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

namespace DeskPRO\Bundle\PortalBundle\Theme;

use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\EventLogger;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zend\Json\Server\Exception\HttpException;

/**
 * Class TagProcessor.
 */
class TagProcessor
{
    /**
     * @var TagRequestFactory
     */
    private $tagRequestFactory;

    /**
     * @var TagHandlerInterface[]
     */
    private $tagHandlers;

    /**
     * @var EventLogger
     */
    private $logger;

    /**
     * @param TagRequestFactory $tagRequestFactory
     * @param array             $tagHandlers
     * @param EventLogger       $logger
     */
    public function __construct(TagRequestFactory $tagRequestFactory, array $tagHandlers, EventLogger $logger)
    {
        $this->tagRequestFactory = $tagRequestFactory;
        $this->tagHandlers       = $tagHandlers;
        $this->logger            = $logger;
    }

    /**
     * @param Tag   $tag
     * @param array $arguments
     *
     * @return string
     */
    public function process(Tag $tag, array $arguments = [])
    {
        $tagRequest = $this->tagRequestFactory->create($tag, $arguments);

        if (!$handler = $this->findHandler($tag, $tagRequest)) {
            throw new \RuntimeException('no handler found for "'.$tag->getName().'"');
        }

        try {
            $response = $handler->handle($tag, $tagRequest);
        } catch (AccessDeniedException $e) {
            $response = '';
        } catch (HttpException $e) {
            $response = '';
        }

        if (!$response) {
            return ''; // be passive and default to blank
        } elseif (!$response->isSuccessful()) {
            $this->logger->log(new \RuntimeException('Unable to render theme content: '.$response->getContent()));

            return ''; // be passive and default to blank
        }

        return $response->getContent();
    }

    /**
     * @param Tag        $tag
     * @param TagRequest $tagRequest
     *
     * @return TagHandlerInterface
     */
    private function findHandler(Tag $tag, TagRequest $tagRequest)
    {
        foreach ($this->tagHandlers as $handler) {
            if ($handler->supports($tag, $tagRequest)) {
                return $handler;
            }
        }
    }
}
