<?php

namespace DeskPRO\Bundle\PortalBundle\Theme;

use DeskPRO\Bundle\PortalBundle\Request\TagRequest;

/**
 * Interface TagHandlerInterface.
 */
interface TagHandlerInterface
{
    /**
     * If the handler can handle the tag.... for instance, ESI handler can only handle ESI tags, but not if its a guest.
     *
     * @param Tag        $tag
     * @param TagRequest $tagRequest
     *
     * @return bool
     */
    public function supports(Tag $tag, TagRequest $tagRequest);

    /**
     * @param Tag        $tag
     * @param TagRequest $tagRequest
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Tag $tag, TagRequest $tagRequest);
}
