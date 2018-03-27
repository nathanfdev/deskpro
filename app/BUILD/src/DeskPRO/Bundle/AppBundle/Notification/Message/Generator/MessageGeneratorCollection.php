<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator;

use DeskPRO\Component\Util\AbstractCollection;

class MessageGeneratorCollection extends AbstractCollection
{
    private $attached_generators = [];

    public function addGenerator(MessageGeneratorInterface $generator)
    {
        if (!$this->hasGenerator($generator)) {
            $this->attached_generators[$generator->getType()] = true;
            $this->collection[]                               = $generator;
        }
    }

    /**
     * @param $generator
     *
     * @return bool
     */
    protected function hasGenerator(MessageGeneratorInterface $generator)
    {
        return array_key_exists($generator->getType(), $this->attached_generators);
    }
}
