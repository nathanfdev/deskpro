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

namespace spec\Application\PortalBundle\Theme;

use Application\DeskPRO\Entity\Article;
use Application\PortalBundle\Theme\Tag;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Application\PortalBundle\Theme\TagRequestFactory;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zend\Ldap\Node\RootDse\eDirectory;

/**
 * @mixin \Application\PortalBundle\Theme\TagRequestFactory
 */
class TagRequestFactorySpec extends ObjectBehavior
{
    function let(
        RequestStack $request_stack,
        Request $request,
        HeaderBag $headers,
        ParameterBag $attributes,
        SessionInterface $session,
        Tag $tag
    )
    {
        $request->headers = $headers;
        $request->attributes = $attributes;
        $request->getSession()->willReturn($session);

        $headers->all()->willReturn(array());
        $attributes->all()->willReturn(array());

        $request_stack->getCurrentRequest()->willReturn($request);

        $tag->getDefaultOptions()->willReturn(array());
        $tag->getName()->willReturn('tag_name');
        $tag->allowRouteParams()->willReturn(true); //default

        $this->beConstructedWith($request_stack);
    }

    function it_returns_a_tag_request_with_an_options_resolver(
        Tag $tag
    )
    {
        $arguments = array();
        $tag_request = $this->create($tag, $arguments);

        $tag_request->shouldHaveType('Application\PortalBundle\Request\TagRequest');
        $tag_request->getOptionsResolver()->shouldHaveType('Symfony\Component\OptionsResolver\Options');
    }

    function it_retains_the_current_requests_session(
        Tag $tag,
        SessionInterface $session
    )
    {
        $arguments = array();
        $tag_request = $this->create($tag, $arguments);

        $tag_request->getSession()->shouldReturn($session);
    }

    function it_returns_a_tag_request_with_the_same_headers_as_current_request(
        Tag $tag,
        HeaderBag $headers
    )
    {
        $headers->all()->willReturn(
            $current_headers = array(
                'these' => array('headers'),
                'x-user-context-hash' => array('something')
            )
        );

        $arguments = array();
        $tag_request = $this->create($tag, $arguments);

        $tag_request->headers->all()->shouldBeLike($current_headers);
    }

    function it_makes_the_query_a_tag_options_array_which_includes_the_tag_name(
        Tag $tag
    )
    {
        $tag->getName()->willReturn('tag_name');
        $tag->getDefaultOptions()->willReturn(array(
            'a' => 'a',
            'b' => 'b',
            'c' => 'c'
        ));

        $tag_request = $this->create($tag, $arguments = array('b' => 'not b'));

        $tag_request->shouldHaveType('Application\PortalBundle\Request\TagRequest');
        $tag_request->query->all()->shouldBeLike(array(
            'tag_options' => array(
                'a' => 'a',
                'b' => 'not b',
                'c' => 'c',
                '_tag_name' => 'tag_name'
            )
        ));
    }

    function it_converts_entity_objects_in_the_query_to_their_id_value_and_rejects_other_objects(
        Tag $tag,
        Article $article
    )
    {
        $tag->getName()->willReturn('tag_name');
        $tag->getDefaultOptions()->willReturn(array(
            'a' => 'a',
            'b' => 'b',
            'c' => 'c'
        ));

        $article->getId()->willReturn(5);
        $tag_request = $this->create($tag, $arguments = array(
            'b' => $article,
            'will_be_ignored' => new \SplStack()
        ));

        $tag_request->shouldHaveType('Application\PortalBundle\Request\TagRequest');
        $tag_request->query->all()->shouldBeLike(array(
            'tag_options' => array(
                'a' => 'a',
                'b' => 5,
                'c' => 'c',
                '_tag_name' => 'tag_name'
            )
        ));
    }

    function it_clones_most_attributes_but_not_all_plus_adds_tag_name(
        Tag $tag,
        ParameterBag $attributes
    )
    {
        $tag->getName()->willReturn('tag_name');

        $attributes->all()->willReturn(
            array(
                'tag_request' => 'this should be removed',
                '_security' => 'anything with leading _ is removed',
                '_test' => 'anything with leading _ is removed',
                '_portal_tag_cache' => 'anything with leading _ is removed',
                '_route' => 'by default this is allowed',
                '_route_params' => 'by default this is allowed',
                'something' => 'this will stay'
            )
        );

        $arguments = array();
        $tag_request = $this->create($tag, $arguments);

        $tag_request->attributes->all()->shouldBeLike(array(
            'something' => 'this will stay',
            '_tag_name' => 'tag_name',
            '_route' => 'by default this is allowed',
            '_route_params' => 'by default this is allowed',
        ));
    }

    function it_will_not_allow_route_attributes_if_tag_says_not_to(
        Tag $tag,
        ParameterBag $attributes
    )
    {
        $tag->getName()->willReturn('tag_name');
        $tag->allowRouteParams()->willReturn(false);

        $attributes->all()->willReturn(
            array(
                'tag_request' => 'this should be removed',
                '_security' => 'anything with leading _ is removed',
                '_test' => 'anything with leading _ is removed',
                '_portal_tag_cache' => 'anything with leading _ is removed',
                '_route' => 'this is removed since tag wont allow it',
                '_route_params' => 'this is removed since tag wont allow it',
                'something' => 'this will stay'
            )
        );

        $arguments = array();
        $tag_request = $this->create($tag, $arguments);

        $tag_request->attributes->all()->shouldBeLike(array(
            'something' => 'this will stay',
            '_tag_name' => 'tag_name'
        ));
    }
}
