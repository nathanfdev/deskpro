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

/**
* DeskPRO
*
* @package DeskPRO
*/

namespace Application\DeskPRO\Command;

use Application\DeskPRO\JobQueue\Processor\IncomingSmsProcessor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DevJobCreateIncomingSmsCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dpdev:job:create-incoming-sms')
            ->setDescription('Creates an incoming job and puts it in the queue')
            ->addArgument('message', InputOption::VALUE_REQUIRED, 'The text message', 'Hello World')
            ->addOption('to', null, InputOption::VALUE_OPTIONAL, 'To Number', '+11111111111')
            ->addOption('from', null, InputOption::VALUE_OPTIONAL, 'From Number', '+12222222222')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Application\DeskPRO\JobQueue\JobQueue $queue */
        $queue = $this->getContainer()->getSystemService('job_queue');

        $queue->add(IncomingSmsProcessor::JOB_TYPE, array(
                'message' => $input->getArgument('message'),
                'to_number' => $input->getOption('to'),
                'from_number' => $input->getOption('from')
            )
        );
    }
}
