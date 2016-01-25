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

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\JobQueue\Processor\MassActions;

use Application\DeskPRO\JobQueue\Processor\AbstractJobProcessor;
use DeskPRO\Bundle\AppBundle\Data\MassActions\MassActionsPreprocessorFactory;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Processes mass actions on publish-like content (articles, news, downloads, feedback).
 *
 * The job data should be an array with the following keys:
 * - ids
 * - actions
 * - content
 */
class PublishProcessor extends AbstractJobProcessor
{
    const JOB_TYPE = 'publish_mass';

    private $em;

    public function __construct(Connection $connection, EntityManager $em)
    {
        parent::__construct($connection);
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function setDataOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(['ids', 'actions', 'content']);
        $resolver->setAllowedTypes(['content' => 'string']);
    }

    /**
     * @param array $data validated data (the payload)
     * @param array $job  the full job db row array
     *
     * @return bool
     */
    public function process(array $data, array $job)
    {
        $preProcessor      = MassActionsPreprocessorFactory::create($this->em, $data);
        $entities          = $preProcessor->selectEntities();
        $actionsCollection = $preProcessor->prepareActions();
        $actions           = $actionsCollection->getActions();

        foreach ($entities as $entity) {
            /** @var \DeskPRO\Bundle\AppBundle\ActionEngine\ActionInterface $action */
            foreach ($actions as $action) {
                $action->run($entity);
                $this->em->persist($entity);
            }
        }
        $this->em->flush();

        return true;
    }
}
