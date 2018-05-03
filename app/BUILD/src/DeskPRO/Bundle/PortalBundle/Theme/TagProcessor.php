<?php

namespace DeskPRO\Bundle\PortalBundle\Theme;

use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use DeskPRO\Component\Util\RegexUtils;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
     * @param TagRequestFactory $tagRequestFactory
     * @param array             $tagHandlers
     */
    public function __construct(TagRequestFactory $tagRequestFactory, array $tagHandlers)
    {
        $this->tagRequestFactory = $tagRequestFactory;
        $this->tagHandlers       = $tagHandlers;
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
        if (!$tagRequest) {
            // unable to get current request
            // return empty content
            return '';
        }

        if (!$handler = $this->findHandler($tag, $tagRequest)) {
            throw new \RuntimeException('no handler found for "'.$tag->getName().'"');
        }

        try {
            $response = $handler->handle($tag, $tagRequest);
        } catch (AccessDeniedException $e) {
            // permission errors
            $response = '';
        } catch (HttpException $e) {
            // http errors like 404
            $response = '';
        } catch (\Exception $e) {
            // See src/Symfony/Component/HttpKernel/HttpCache/Esi.php
            // A redirect also causes an exception. We should blank these out.
            // In portal they're typically auto-redirections based on permission
            // (e.g. sidebar visible on login page, but no access to say, news, which is in the sidebar)
            $statusCode = RegexUtils::getMatch(
                '/Error when rendering ".*?" \(Status code is (?P<statusCode>\d+)\)./',
                $e->getMessage(),
                'statusCode'
            );

            $statusCode = $statusCode ? (int) $statusCode : null;

            if ($statusCode && $statusCode >= 300 && $statusCode < 500) {
                $response = '';
            } else {
                SystemErrorHandler::logException($e);
                $response = '';
            }
        }

        if (!$response || ($response->getStatusCode() >= 300 && $response->getStatusCode() < 400)) {
            return ''; // be passive and default to blank
        } elseif (!$response->isSuccessful()) {
            if ($response->getStatusCode() >= 500) {
                SystemErrorHandler::logException(new \RuntimeException('Unable to render theme content (status '.$response->getStatusCode().') : '.$response->getContent()));
            }

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
