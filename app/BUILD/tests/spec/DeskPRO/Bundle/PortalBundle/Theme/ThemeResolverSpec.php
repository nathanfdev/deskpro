<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace spec\DeskPRO\Bundle\PortalBundle\Theme;

use DeskPRO\Bundle\PortalBundle\Theme\Tag;
use DeskPRO\Bundle\PortalBundle\Theme\TagProcessor;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeInterface;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeRepository;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\Theme\ThemeResolver
 */
class ThemeResolverSpec extends ObjectBehavior
{
    public function let(
        ContainerInterface $container,
        ThemeRepository $theme_repo,
        TagProcessor $tag_processor,
        EntityManager $em,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($container, $theme_repo, $tag_processor, $em, $logger);
    }

    public function it_processes_a_tag_by_handing_off_to_the_tag_processor(
        TagProcessor $tag_processor,
        ThemeInterface $theme,
        Tag $tag
    ) {
        $args = ['some' => 'args'];

        $theme->resolveTag('knowledgebase')->willReturn($tag);

        $tag_processor->process($tag, $args)->willReturn('result');

        $this->processTag($theme, 'knowledgebase', $args)->shouldReturn('result');
    }

    public function it_returns_blank_string_if_tag_is_not_found_in_theme(
        TagProcessor $tag_processor,
        ThemeInterface $theme
    ) {
        $args = ['some' => 'args'];

        $theme->resolveTag('knowledgebase')->willReturn(null);

        $tag_processor->process(Argument::any(), $args)->shouldNotBeCalled();
    }
}
