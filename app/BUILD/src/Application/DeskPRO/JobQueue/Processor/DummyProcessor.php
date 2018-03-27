<?php

namespace Application\DeskPRO\JobQueue\Processor;

use Symfony\Component\OptionsResolver\OptionsResolver;

class DummyProcessor extends AbstractJobProcessor
{
    const JOB_TYPE = 'dummy';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data, array $job)
    {
        return true;
    }
}
