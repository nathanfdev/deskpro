<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder;

use Application\DeskPRO\Cache\CacheAdapterInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheckSerializer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheckCacher
 */
class PhpCheckCacherSpec extends ObjectBehavior
{
    public function let(
        CacheAdapterInterface $adapter,
        PhpCheckSerializer $serializer,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($adapter, $serializer, $logger);
    }

    public function it_will_retrieve_a_cached_query(
        PhpCheck $compiled,
        PhpCheckSerializer $serializer,
        CacheAdapterInterface $adapter
    ) {
        $adapter->get('php.term_engine.php_check.'.'some key from engine')->shouldBeCalled();
        $serializer->unserialize(Argument::any())->willReturn($compiled);

        $this->fetchCheck('some key from engine')->shouldReturn($compiled);
    }

    public function it_will_cache_a_query(
        PhpCheck $compiled,
        PhpCheckSerializer $serializer,
        CacheAdapterInterface $adapter
    ) {
        $serializer->serialize($compiled)->willReturn($serialized_version = 'serialized_version');

        $this->saveCheck('some key from engine', $compiled);

        $adapter->set('php.term_engine.php_check.'.'some key from engine', $serialized_version)->shouldHaveBeenCalled();
    }
}
