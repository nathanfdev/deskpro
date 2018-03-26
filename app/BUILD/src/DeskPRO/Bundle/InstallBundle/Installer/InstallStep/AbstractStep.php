<?php

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use DeskPRO\Bundle\InstallBundle\Installer\InstallerContext;
use Symfony\Component\Console\Helper;
use Symfony\Component\Console\Question\Question;

abstract class AbstractStep
{
    /**
     * @var InstallerContext
     */
    private $context;

    /**
     * @var bool
     */
    private $is_failed;

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
     * Mark this step as failed.
     */
    public function markAsFailed()
    {
        $this->is_failed = true;
    }

    /**
     * Check if the step has failed.
     *
     * @return bool
     */
    public function isFailed()
    {
        return $this->is_failed;
    }

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
        $this->context->getOutput()->writeln($messages, $options);
    }

    /**
     * @param string|string[] $messages
     * @param bool            $newline
     * @param int             $options
     */
    public function write($messages, $newline = false, $options = 0)
    {
        $this->context->getOutput()->write($messages, $newline, $options);
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
     * @param Question $q
     * @param string   $id
     *
     * @return string
     */
    public function askQuestion(Question $q, $id = null)
    {
        $p = $this->getContext()->getProfile();
        if ($p->hasAnswer($id)) {
            $val = $p->getAnswer($id);
            $this->write($q->getQuestion());
            if ($q->isHidden()) {
                $this->write(str_repeat('*', strlen($val)));
            } else {
                $this->write($val);
            }
            $this->writeln('');

            if ($validate = $q->getValidator()) {
                try {
                    $val = call_user_func($validate, $val);
                } catch (\Exception $e) {
                    $this->writeln($e->getMessage());
                    // ask without id to prompt the user
                    return $this->askQuestion($q);
                }
            }

            return $val;
        }

        if (!$this->getInput()->isInteractive() && $q->getDefault() === null) {
            throw new \RuntimeException('Non-interactive mode but we do not have an answer to: '.$q->getQuestion());
        }

        return $this->getQuestionHelper()->ask($this->getInput(), $this->getOutput(), $q);
    }

    /**
     * @param bool|null $default
     *
     * @return bool
     */
    public function askConfirm($default = null)
    {
        if ($default === true) {
            $q = new Question('[Y/n]> ', 'Y');
        } elseif ($default === false) {
            $q = new Question('[y/N]> ', 'n');
        } else {
            $q = new Question('[y/n]> ');
        }

        $q->setValidator(function ($v) {
            $v = strtolower($v);
            if ($v === 'no' || $v === 'false' || $v === 'f') {
                $v = 'n';
            }
            if ($v === 'yes' || $v === 'true' || $v === 't') {
                $v = 't';
            }

            if ($v != 'n' && $v !== 'y') {
                throw new \Exception("Please enter 'Y' for Yes or 'N' for No.");
            }

            return $v;
        });

        return $this->askQuestion($q) === 'y';
    }

    /**
     * @param int $max
     *
     * @return Helper\ProgressBar
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

    /**
     * @param string $title
     */
    public function writeBigTitle($title)
    {
        $line_len  = 70;
        $title_len = strlen($title);
        $half      = floor(($line_len - $title_len) / 2);

        $left  = $half;
        $right = $half;

        if ($left + $right + $title_len < $line_len) {
            ++$right;
        }

        $fmt = '<bg=blue;fg=white;options=bold>';
        $str = $fmt.str_repeat(' ', $line_len).'</>'
            .PHP_EOL
            .$fmt.str_repeat(' ', $left).$title.str_repeat(' ', $right).'</>'
            .PHP_EOL
            .$fmt.str_repeat(' ', $line_len).'</>'
            .PHP_EOL;

        $this->writeln($str);
    }

    public function writeBoundary($text, $style = false)
    {
        $line_len  = 70;
        $title_len = strlen($text);
        $half      = floor(($line_len - $title_len) / 2);

        $left  = $half;
        $right = $half;

        if ($left + $right + $title_len < $line_len) {
            ++$right;
        }
        $str = str_repeat('#', $left - 1).' '.$text.' '.str_repeat('#', $right - 1);

        if ($style) {
            $str = "<{$style}>{$str}</{$style}>";
        }
        $this->writeln($str);
    }
}
