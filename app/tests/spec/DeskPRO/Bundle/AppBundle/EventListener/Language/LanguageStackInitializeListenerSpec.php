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

namespace spec\DeskPRO\Bundle\AppBundle\EventListener\Language;

use Application\DeskPRO\Entity\Language;
use DeskPRO\Bundle\AppBundle\EventListener\Language\LastLanguageListener;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Language\LanguageStack;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\AppBundle\EventListener\Language\LanguageStackInitializeListener;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Psr\Log\LoggerInterface;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\EventListener\Language\LanguageStackInitializeListener
 */
class LanguageStackInitializeListenerSpec extends ObjectBehavior
{
    function let(
        LanguageStack $language_stack,
        LanguageManager $language_manager,
        GetResponseEvent $event,
        Request $request,
        ParameterBag $cookies,
        ParameterBag $query,
        LoggerInterface $logger
    )
    {
        $event->isMasterRequest()->willReturn(true);
        $request->cookies = $cookies;
        $request->query = $query;
        $event->getRequest()->willReturn($request);
        $language_manager->getLanguageStack()->willReturn($language_stack);

        $this->beConstructedWith($language_manager, $logger);
    }

    function it_is_a_request_listener_with_very_high_priority()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
        $this->getSubscribedEvents()->shouldReturn(array(
            KernelEvents::REQUEST => array('onRequest', 512)
        ));
    }

    function it_only_listens_to_master_request(
        GetResponseEvent $event,
        LanguageStack $language_stack
    )
    {
        $event->isMasterRequest()->willReturn(false);

        $language_stack->push(Argument::any())->shouldNotBeCalled();
        $language_stack->pushDefault()->shouldNotBeCalled();

        $this->onRequest($event);
    }

    function it_pushes_lang_from_uri_first_if_exists(
        LanguageStack $language_stack,
        LanguageManager $language_manager,
        Language $en,
        GetResponseEvent $event,
        Request $request
    )
    {
        $language_manager->getLanguage('en')->willReturn($en);
        $language_stack->push($en)->shouldBeCalled();

        $request->getPathInfo()->willReturn('/en/articles');
        $this->onRequest($event);
    }

    function it_wont_push_from_uri_if_lang_code_is_misplaced_or_malformed(
        LanguageStack $language_stack,
        LanguageManager $language_manager,
        Language $en,
        GetResponseEvent $event,
        Request $request
    )
    {
        $language_manager->getLanguage('en')->willReturn($en);
        $language_stack->push($en)->shouldNotBeCalled();
        $language_stack->pushDefault()->shouldBeCalled();

        $request->getPathInfo()->willReturn('/english/articles');
        $this->onRequest($event);

        $request->getPathInfo()->willReturn('/articles/en');
        $this->onRequest($event);
    }

    function it_pushes_the_last_lang_cookie_if_it_exists(
        LanguageStack $language_stack,
        LanguageManager $language_manager,
        Language $fr,
        GetResponseEvent $event,
        Request $request,
        ParameterBag $cookies
    )
    {
        $cookies->get(\DeskPRO\Bundle\AppBundle\EventListener\Language\LastLanguageListener::COOKIE_NAME)->willReturn('fr');
        $language_manager->getLanguage('fr')->willReturn($fr);

        $language_stack->push($fr)->shouldBeCalled();

        $this->onRequest($event);
    }

    function it_pushes_the_get_variable_from_esi_calls_if_exists_and_no_cookie(
        LanguageStack $language_stack,
        LanguageManager $language_manager,
        Language $fr,
        GetResponseEvent $event,
        Request $request,
        ParameterBag $query
    )
    {
        $query->get('lang_url_code')->willReturn('fr');
        $request->getPathInfo()->willReturn('/_proxy?some=params&lang_url_code=fr');
        $language_manager->getLanguage('fr')->willReturn($fr);

        $language_stack->push($fr)->shouldBeCalled();

        $this->onRequest($event);
    }

    function it_does_not_push_the_special_query_param_if_not_a_proxy_request(
        LanguageStack $language_stack,
        LanguageManager $language_manager,
        Language $fr,
        GetResponseEvent $event,
        Request $request,
        ParameterBag $query
    )
    {
        $query->get('lang_url_code')->willReturn('fr');
        $request->getPathInfo()->willReturn('/articles');
        $language_manager->getLanguage('fr')->willReturn($fr);

        $language_stack->push($fr)->shouldNotBeCalled();
        $language_stack->pushDefault()->shouldBeCalled();

        $this->onRequest($event);
    }

    function it_pushes_default_if_no_other_info_to_get_lang_from(
        LanguageStack $language_stack,
        GetResponseEvent $event
    )
    {
        $language_stack->pushDefault()->shouldBeCalled();

        $this->onRequest($event);
    }
}
