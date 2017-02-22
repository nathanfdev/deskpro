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

namespace spec\DeskPRO\Bundle\AppBundle\Helper;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\News;
use DeskPRO\Bundle\PortalBundle\Model\TicketFilter;
use PhpSpec\ObjectBehavior;

class ArbitraryHasherSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('DeskPRO\Bundle\AppBundle\Helper\ArbitraryHasher');
    }

    public function it_can_hash_a_single_scalar()
    {
        $hash = $this->generateHash('hi')->shouldBeString();

        $hash = $this->generateHash('hi');
        $this->generateHash('hi')->shouldReturn($hash);

        $hash = $this->generateHash(2);
        $this->generateHash(2)->shouldReturn($hash);

        $hash = $this->generateHash(4.56);
        $this->generateHash(4.56)->shouldReturn($hash);
    }

    public function it_can_hash_an_array_of_scalars()
    {
        $standard_input             = ['hi there', 9, 4.78];
        $different_input            = ['hi there!', 9, 4.78];
        $reorganized_standard_input = ['hi there', 9, 4.78];

        $hash = $this->generateHash($standard_input)->shouldBeString();

        $hash      = $this->generateHash($standard_input);
        $next_hash = $this->generateHash($standard_input);
        $hash->shouldEqual($next_hash);

        $hash      = $this->generateHash($standard_input);
        $next_hash = $this->generateHash($reorganized_standard_input);
        $hash->shouldEqual($next_hash);

        $hash      = $this->generateHash($standard_input);
        $next_hash = $this->generateHash($different_input);
        $hash->shouldNotEqual($next_hash);
    }

    public function it_can_hash_an_entity(News $news1, News $news2, Article $article)
    {
        $news1->getId()->willReturn(1);
        $news2->getId()->willReturn(3);
        $article->getId()->willReturn(1);

        $hash = $this->generateHash($news1)->shouldBeString();

        $hash      = $this->generateHash($news1);
        $next_hash = $this->generateHash($news1);
        $hash->shouldEqual($next_hash);

        $hash      = $this->generateHash($article);
        $next_hash = $this->generateHash($article);
        $hash->shouldEqual($next_hash);

        $hash      = $this->generateHash($news1);
        $next_hash = $this->generateHash($news2);
        $hash->shouldNotEqual($next_hash);

        $hash      = $this->generateHash($article);
        $next_hash = $this->generateHash($news2);
        $hash->shouldNotEqual($next_hash);
    }

    public function it_can_hash_plain_objects_too()
    {
        $object1      = new \stdClass();
        $object2      = new stdClassChild();
        $object3      = new \stdClass();
        $object3->foo = 'baz';

        $hash = $this->generateHash($object1)->shouldBeString();

        $hash      = $this->generateHash($object1);
        $next_hash = $this->generateHash($object1);
        $hash->shouldEqual($next_hash);

        $hash      = $this->generateHash($object1);
        $next_hash = $this->generateHash($object2);
        $hash->shouldNotEqual($next_hash);

        $hash      = $this->generateHash($object1);
        $next_hash = $this->generateHash($object3);
        $hash->shouldNotEqual($next_hash);
    }

    public function it_can_hash_a_mixed_array(News $news1, News $news2, Article $article, TicketFilter $filter)
    {
        $object1 = new \stdClass();

        $news1->getId()->willReturn(1);
        $news2->getId()->willReturn(3);
        $article->getId()->willReturn(1);

        $standard_array             = [$object1, $news1, $news2, $article, $filter, 'hi there'];
        $standard_reorganized_array = [$object1, $news1, $news2, $article, $filter, 'hi there'];
        $different_array            = [$object1, $news1, $news2, $article, $filter, 'chris tickner'];

        $hash = $this->generateHash($standard_array)->shouldBeString();
        $hash = $this->generateHash($different_array)->shouldBeString();

        $hash      = $this->generateHash($standard_array);
        $next_hash = $this->generateHash($standard_array);
        $hash->shouldEqual($next_hash);

        $hash      = $this->generateHash($standard_array);
        $next_hash = $this->generateHash($standard_reorganized_array);
        $hash->shouldEqual($next_hash);

        $hash      = $this->generateHash($standard_array);
        $next_hash = $this->generateHash($different_array);
        $hash->shouldNotEqual($next_hash);
    }
}

class StdClassChild extends \StdClass
{
}
