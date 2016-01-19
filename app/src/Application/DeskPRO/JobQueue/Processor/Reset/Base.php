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

namespace Application\DeskPRO\JobQueue\Processor\Reset;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\JobQueue\Processor\AbstractJobProcessor;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
    public function setDataOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'context_person_id' => null,
            'limit'             => 100,
            'offset'            => 0,
        ));
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

            $jobs = $this->em->getRepository('DeskPRO:Job')->findBy(array('depends_on_job' => $job['id']));
            foreach ($jobs as $dep) {
                $dep->depends_on_job = $new;
            }
            $this->em->flush();

            $this->em->getRepository('DeskPRO:Ticket')->fillSearchTable();
        }

        return true;
    }

    abstract protected function doProcess(array $data);
}
