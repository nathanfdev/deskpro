<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\DeskPRO\JobQueue\Processor\Purge;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\JobQueue\JobQueue;
use Application\DeskPRO\JobQueue\Processor\AbstractJobProcessor;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class Base extends AbstractJobProcessor
{
    /**
     * @var JobQueue
     */
    protected $queue;

    /**
     * @inheritdoc
     */
    public function __construct(Connection $connection, JobQueue $queue)
    {
        parent::__construct($connection);
        $this->queue = $queue;
    }

    /**
     * @inheritdoc
     */
    public function setDataOptions(OptionsResolverInterface $resolver)
    {

    }

    /**
     * @inheritdoc
     */
    public function process(array $data, array $job)
    {
        if (!$this->doProcess($data, $job)) {
            $this->queue->retryByJobId($job['id'], new \DateTime('+5 seconds'));
        } else {
            return true;
        }
    }

    abstract protected function doProcess(array $data);
}