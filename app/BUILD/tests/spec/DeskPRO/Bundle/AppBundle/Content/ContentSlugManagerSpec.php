<?php

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
        $news_repo->findOneBy(['slug' => $slug])->willReturn($news);

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

        $news_repo->findOneBy(['slug' => $slug])->willReturn(null);
        $news_slug_history_repo->findOneBy(['slug' => $slug])->willReturn($news_history);

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
