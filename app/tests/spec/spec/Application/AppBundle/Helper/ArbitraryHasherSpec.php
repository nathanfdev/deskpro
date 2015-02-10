<?php

namespace spec\Application\AppBundle\Helper;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\BrandSetting;
use Application\DeskPRO\Entity\News;
use Application\PortalBundle\Helper\PortalRatingsHelper;
use Application\PortalBundle\Model\TicketFilter;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ArbitraryHasherSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Application\AppBundle\Helper\ArbitraryHasher');
    }

    function it_can_hash_a_single_scalar()
    {
        $hash = $this->generateHash('hi')->shouldBeString();

        $hash = $this->generateHash('hi');
        $this->generateHash('hi')->shouldReturn($hash);

        $hash = $this->generateHash(2);
        $this->generateHash(2)->shouldReturn($hash);

        $hash = $this->generateHash(4.56);
        $this->generateHash(4.56)->shouldReturn($hash);
    }

    function it_can_hash_an_array_of_scalars()
    {
        $standard_input = array('hi there', 9, 4.78);
        $different_input = array('hi there!', 9, 4.78);
        $reorganized_standard_input = array(9, 'hi there', 4.78);

        $hash = $this->generateHash($standard_input)->shouldBeString();

        $hash = $this->generateHash($standard_input);
        $next_hash = $this->generateHash($standard_input);
        $hash->shouldEqual($next_hash);

        $hash = $this->generateHash($standard_input);
        $next_hash = $this->generateHash($reorganized_standard_input);
        $hash->shouldEqual($next_hash);

        $hash = $this->generateHash($standard_input);
        $next_hash = $this->generateHash($different_input);
        $hash->shouldNotEqual($next_hash);
    }

    function it_can_hash_an_entity(News $news1, News $news2, Article $article)
    {
        $news1->getId()->willReturn(1);
        $news2->getId()->willReturn(3);
        $article->getId()->willReturn(1);

        $hash = $this->generateHash($news1)->shouldBeString();

        $hash = $this->generateHash($news1);
        $next_hash = $this->generateHash($news1);
        $hash->shouldEqual($next_hash);

        $hash = $this->generateHash($article);
        $next_hash = $this->generateHash($article);
        $hash->shouldEqual($next_hash);

        $hash = $this->generateHash($news1);
        $next_hash = $this->generateHash($news2);
        $hash->shouldNotEqual($next_hash);

        $hash = $this->generateHash($article);
        $next_hash = $this->generateHash($news2);
        $hash->shouldNotEqual($next_hash);
    }

    function it_can_hash_plain_objects_too()
    {
        $object1 = new \stdClass();
        $object2 = new stdClassChild();
        $object3 = new \stdClass();
        $object3->foo = 'baz';

        $hash = $this->generateHash($object1)->shouldBeString();

        $hash = $this->generateHash($object1);
        $next_hash = $this->generateHash($object1);
        $hash->shouldEqual($next_hash);

        $hash = $this->generateHash($object1);
        $next_hash = $this->generateHash($object2);
        $hash->shouldNotEqual($next_hash);

        $hash = $this->generateHash($object1);
        $next_hash = $this->generateHash($object3);
        $hash->shouldNotEqual($next_hash);
    }

    function it_can_hash_a_mixed_array(News $news1, News $news2, Article $article, TicketFilter $filter)
    {
        $object1 = new \stdClass();

        $news1->getId()->willReturn(1);
        $news2->getId()->willReturn(3);
        $article->getId()->willReturn(1);

        $standard_array = array($object1, $news1, $news2, $article, $filter, 'hi there');
        $standard_reorganized_array = array($news1, 'hi there', $object1, $filter, $news2, $article);
        $different_array = array($object1, $news1, $news2, $article, $filter, 'chris tickner');

        $hash = $this->generateHash($standard_array)->shouldBeString();
        $hash = $this->generateHash($different_array)->shouldBeString();

        $hash = $this->generateHash($standard_array);
        $next_hash = $this->generateHash($standard_array);
        $hash->shouldEqual($next_hash);

        $hash = $this->generateHash($standard_array);
        $next_hash = $this->generateHash($standard_reorganized_array);
        $hash->shouldEqual($next_hash);

        $hash = $this->generateHash($standard_array);
        $next_hash = $this->generateHash($different_array);
        $hash->shouldNotEqual($next_hash);
    }
}

class StdClassChild extends \StdClass
{
}
