<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace spec\DeskPRO\Bundle\AppBundle\Content;

use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsSlugHistory;
use Application\DeskPRO\EntityRepository\News as NewsRepo;
use Application\DeskPRO\EntityRepository\NewsSlugHistory as NewsSlugHistoryRepo;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContentSlugManagerSpec extends ObjectBehavior
{
    public function let(ContainerInterface $container, EntityManager $em)
    {
        $container->get('doctrine.orm.default_entity_manager')->willReturn($em);

        $this->beConstructedWith($container);
    }

    public function it_can_find_content_by_slug(
        EntityManager $em,
        News $news,
        NewsRepo $news_repo
    ) {
        $slug          = 'big-announcement';
        $content_class = 'Application\DeskPRO\Entity\News';

        $em->getRepository($content_class)->willReturn($news_repo);
        $news_repo->findOneBy(array('slug' => $slug))->willReturn($news);

        $this->findContentObjectBySlug($slug, $content_class)->shouldReturn($news);
    }

    public function it_will_find_content_by_slug_even_if_it_is_historical(
        EntityManager $em,
        News $news,
        NewsSlugHistory $news_history,
        NewsRepo $news_repo,
        NewsSlugHistoryRepo $news_slug_history_repo
    ) {
        $slug          = 'big-announcement';
        $content_class = 'Application\DeskPRO\Entity\News';

        $news_history->getContent()->willReturn($news);

        $em->getRepository($content_class)->willReturn($news_repo);
        $em->getRepository($content_class.'SlugHistory')->willReturn($news_slug_history_repo);

        $news_repo->findOneBy(array('slug' => $slug))->willReturn(null);
        $news_slug_history_repo->findOneBy(array('slug' => $slug))->willReturn($news_history);

        $this->findContentObjectBySlug($slug, $content_class)->shouldReturn($news);
    }

    public function it_will_not_return_a_new_history_object_if_the_current_slug_is_already_valid(
        News $news
    ) {
        $news->getTitle()->willReturn('Current Slug!');
        $news->getSlug()->willReturn('current-slug');

        $this->ensureValidSlug($news)->shouldBe(null);
    }
}
