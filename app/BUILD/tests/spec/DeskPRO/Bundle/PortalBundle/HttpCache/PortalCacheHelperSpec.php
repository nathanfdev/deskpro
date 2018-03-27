<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\HttpCache;

use DeskPRO\Bundle\PortalBundle\HttpCache\PortalHttpCache;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\HttpCache\PortalCacheHelper
 */
class PortalCacheHelperSpec extends ObjectBehavior
{
    public function let(RequestStack $request_stack, TokenStorageInterface $tokenStorage, Request $request, HeaderBag $headers)
    {
        $headers->has(PortalHttpCache::USER_CONTEXT_HASH_HEADER)->willReturn(true);
        $headers->get(PortalHttpCache::USER_CONTEXT_HASH_HEADER)->willReturn('context_hash');
        $request->headers = $headers;
        $request_stack->getMasterRequest()->willReturn($request);

        $tokenStorage->getToken()->willReturn(null);

        $this->beConstructedWith($request_stack, $tokenStorage);
    }

    public function it_can_get_you_the_master_request_user_context_hash_if_exists()
    {
        $this->getUserContextHash()->shouldReturn('context_hash');
    }

    public function it_knows_if_given_hash_is_a_guest_request_or_not()
    {
        $this->isGuestHash(PortalHttpCache::ANON_NO_SESSION_HASH)->shouldBe(true);
        $this->isGuestHash(PortalHttpCache::GUEST_WITH_SESSION_HASH)->shouldBe(false); // session = not guest

        $this->isGuestHash('xyz-gibberish')->shouldBe(false);
    }

    public function it_will_not_be_a_guest_request_if_master_request_does_not_have_a_context_hash(
        HeaderBag $headers
    ) {
        // this will happen if the portal cache gets turned off completely
        $headers->has(PortalHttpCache::USER_CONTEXT_HASH_HEADER)->willReturn(false);

        $this->isGuestRequest()->shouldReturn(false);
    }

    public function it_determines_a_guest_request_if_master_request_context_hash_indicates_anonymous(
        HeaderBag $headers
    ) {
        $headers->get(PortalHttpCache::USER_CONTEXT_HASH_HEADER)->willReturn(PortalHttpCache::ANON_NO_SESSION_HASH);

        $this->isGuestRequest()->shouldReturn(true);
    }

    public function it_is_not_a_guest_request_if_master_request_context_hash_indicates_guest_with_session_hash(
        HeaderBag $headers
    ) {
        $headers->get(PortalHttpCache::USER_CONTEXT_HASH_HEADER)->willReturn(PortalHttpCache::GUEST_WITH_SESSION_HASH);

        $this->isGuestRequest()->shouldReturn(false);  // session = not guest
    }

    public function it_determines_not_a_guest_request_if_master_request_context_hash_does_not_look_like_guest(
        HeaderBag $headers
    ) {
        $headers->get(PortalHttpCache::USER_CONTEXT_HASH_HEADER)->willReturn('something that is not anon or guest hash');

        $this->isGuestRequest()->shouldReturn(false);
    }
}
