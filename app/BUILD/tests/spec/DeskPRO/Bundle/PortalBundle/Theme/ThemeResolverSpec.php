<?php

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
