<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\JobQueue\Processor\AbstractJobProcessor;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class Base extends AbstractJobProcessor
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var DeskproContainer
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function __construct(DeskproContainer $container)
    {
        parent::__construct($container->getEm()->getConnection());
        $this->em        = $container->getEm();
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'context_person_id' => null,
            'limit'             => 100,
            'offset'            => 0,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data, array $job)
    {
        $total = (int) $this->doProcess($data, $job);

        if ($total === $data['limit']) {
            $data['offset'] = $data['offset'] + $total;
            $new            = $this->container->getJobQueue()->add($this::JOB_TYPE, $data);

            $jobs = $this->em->getRepository('DeskPRO:Job')->findBy(['depends_on_job' => $job['id']]);
            foreach ($jobs as $dep) {
                $dep->depends_on_job = $new;
            }
            $this->em->flush();
        }

        $this->em->getRepository('DeskPRO:Ticket')->fillSearchTable();

        return true;
    }

    /**
     * @param array $data
     */
    abstract protected function doProcess(array $data);
}
