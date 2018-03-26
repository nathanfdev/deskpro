<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

class AgentsProcessor extends UsersProcessor
{
    const JOB_TYPE = 'reset.agents';

    /**
     * {@inheritdoc}
     */
    protected function getPersons(array $data)
    {
        $limit  = (int) @$data['limit'];
        $offset = (int) @$data['offset'];
        $rep    = $this->em->getRepository('DeskPRO:Person');

        return $rep->findBy(['is_agent' => true], null, $limit, $offset);
    }
}
