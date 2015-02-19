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

namespace spec\Application\PortalBundle\HttpCache;

use Application\PortalBundle\HttpCache\PortalHttpCache;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Application\PortalBundle\HttpCache\PortalCacheHelper;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @mixin \Application\PortalBundle\HttpCache\PortalCacheHelper
 */
class PortalCacheHelperSpec extends ObjectBehavior
{
    function let(RequestStack $request_stack, Request $request, HeaderBag $headers)
    {
        $headers->has(PortalHttpCache::USER_CONTEXT_HASH_HEADER)->willReturn(true);
        $headers->get(PortalHttpCache::USER_CONTEXT_HASH_HEADER)->willReturn('context_hash');
        $request->headers = $headers;
        $request_stack->getMasterRequest()->willReturn($request);

        $this->beConstructedWith($request_stack);
    }

    function it_can_get_you_the_master_request_user_context_hash_if_exists()
    {
        $this->getUserContextHash()->shouldReturn('context_hash');
    }

    function it_knows_if_given_hash_is_a_guest_request_or_not()
    {
        $this->isGuestHash(PortalHttpCache::ANON_HASH)->shouldBe(true);
        $this->isGuestHash(PortalHttpCache::GUEST_HASH)->shouldBe(true);

        $this->isGuestHash('xyz-gibberish')->shouldBe(false);
    }

    function it_defaults_to_guest_request_if_master_request_does_not_have_a_context_hash(
        HeaderBag $headers
    )
    {
        $headers->has(PortalHttpCache::USER_CONTEXT_HASH_HEADER)->willReturn(false);

        $this->isGuestRequest()->shouldReturn(true);
    }

    function it_determines_a_guest_request_if_master_request_context_hash_indicates_anonymous(
        HeaderBag $headers
    )
    {
        $headers->get(PortalHttpCache::USER_CONTEXT_HASH_HEADER)->willReturn(PortalHttpCache::ANON_HASH);

        $this->isGuestRequest()->shouldReturn(true);
    }

    function it_determines_a_guest_request_if_master_request_context_hash_indicates_guest_hash(
        HeaderBag $headers
    )
    {
        $headers->get(PortalHttpCache::USER_CONTEXT_HASH_HEADER)->willReturn(PortalHttpCache::GUEST_HASH);

        $this->isGuestRequest()->shouldReturn(true);
    }

    function it_determines_not_a_guest_request_if_master_request_context_hash_does_not_look_like_guest(
        HeaderBag $headers
    )
    {
        $headers->get(PortalHttpCache::USER_CONTEXT_HASH_HEADER)->willReturn('something that is not anon or guest hash');

        $this->isGuestRequest()->shouldReturn(false);
    }
}
