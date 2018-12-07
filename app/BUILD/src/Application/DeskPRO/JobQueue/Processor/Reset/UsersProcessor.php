<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

use Application\DeskPRO\People\Purger;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsersProcessor extends Base
{
    const JOB_TYPE = 'reset.users';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'context_person_id' => null,
            'limit'             => 1000,
            'offset'            => 0,
            'labeled_by'        => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $count = 0;

        foreach ($this->getPersons($data) as $person) {
            ++$count;
            if (@$data['context_person_id'] === $person['id']) {
                continue;
            }
            $purger = new Purger($person, $this->em, $this->container->get('blob.storage'));
            $purger->purge();
        }

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPersons(array $data)
    {
        $limit  = (int) @$data['limit'];
        $offset = (int) @$data['offset'];
        $rep    = $this->em->getRepository('DeskPRO:Person');

        if ($data['labeled_by']) {
            return $this->em->createQuery('SELECT p FROM DeskPRO:Person p JOIN p.labels l WHERE l.label = :label')
                ->setParameter('label', $data['labeled_by'])
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->getResult();
        }

        return $rep->findBy(['is_agent' => false], null, $limit, $offset);
    }
}
