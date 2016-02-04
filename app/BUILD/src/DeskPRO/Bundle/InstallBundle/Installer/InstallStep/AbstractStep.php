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

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use DeskPRO\Bundle\InstallBundle\Installer\InstallerContext;
use Symfony\Component\Console\Helper;

abstract class AbstractStep
{
    /**
     * @var InstallerContext
     */
    private $context;

    /**
     * AbstractStep constructor.
     *
     * @param InstallerContext $context
     */
    public function __construct(InstallerContext $context)
    {
        $this->context = $context;
    }

    /**
     * Run the step.
     */
    abstract public function run();

    /**
     * Should check the current session to determine if the step has been complete already.
     *
     * @return bool
     */
    abstract public function isComplete();

    /**
     * @return InstallerContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return \DeskPRO\Bundle\InstallBundle\InstallSession\InstallSession
     */
    public function getSession()
    {
        return $this->context->getSession();
    }

    /**
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutput()
    {
        return $this->context->getOutput();
    }

    /**
     * @param string|string[] $messages
     * @param int             $options
     */
    public function writeln($messages, $options = 0)
    {
        $this->context->getOutput()->writeln($messages, $options = 0);
    }

    /**
     * @param string|string[] $messages
     * @param bool            $newline
     * @param int             $options
     */
    public function write($messages, $newline = false, $options = 0)
    {
        $this->context->getOutput()->write($messages, $newline = false, $options = 0);
    }

    /**
     * @return \Symfony\Component\Console\Input\InputInterface
     */
    public function getInput()
    {
        return $this->context->getInput();
    }

    /**
     * @return \Symfony\Component\Console\Helper\FormatterHelper
     */
    public function getFormatterHelper()
    {
        return $this->context->getHelperSet()->get('formatter');
    }

    /**
     * @return \Symfony\Component\Console\Helper\QuestionHelper
     */
    public function getQuestionHelper()
    {
        return $this->context->getHelperSet()->get('question');
    }

    /**
     * @param int $max
     *
     * @return ProgressBar
     */
    public function createProgressBar($max = 0)
    {
        $progress = new Helper\ProgressBar($this->getOutput(), $max);

        return $progress;
    }

    /**
     * @return Helper\Table
     */
    public function createTable()
    {
        $table = new Helper\Table($this->getOutput());

        return $table;
    }
}
