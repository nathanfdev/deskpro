<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\DevBundle\Command\Lang;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemovePhrasesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:lang:remove-phrases')
            ->setDescription('Removes phrases from the default English lang file(s).')
            ->addOption('only', 's', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Comma-seperated list of /regex/ patterns that must match')
            ->addOption('except', 'k', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Comma-seperated list of /regex/ patterns that must not match')
            ->addOption('apply', 'x', InputOption::VALUE_NONE, 'Write changes to disk. Without this, it is just a preview mode')
            ->addArgument('phrases', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'A comma-seperated list of phrases or /regex/ patterns, or a @/path/to/file which includes phrases one per line.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //TODO
    }
}
