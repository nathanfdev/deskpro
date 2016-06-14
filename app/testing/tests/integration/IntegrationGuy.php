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

// You should not change it manually as it will be overwritten on next build
// @codingStandardsIgnoreFile


use \Codeception\Maybe;
use Codeception\Module\Filesystem;

/**
 * Inherited methods.
 *
 * @method void execute($callable)
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void offsetGet($offset)
 * @method void offsetSet($offset, $value)
 * @method void offsetExists($offset)
 * @method void offsetUnset($offset)
 */
class IntegrationGuy extends \Codeception\AbstractGuy
{
    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     *
     * @see Codeception\Module\DpControlHelper::getSymfonyContainer()
     *
     * @return \Codeception\Maybe
     */
    public function getSymfonyContainer()
    {
        $this->scenario->addStep(new \Codeception\Step\Action('getSymfonyContainer', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Sets the database set to a version of the set. If it already exists,
     * it will be re-used (not recreated).
     *
     * @param string $set_name
     *
     * @see Codeception\Module\DpControlHelper::enableDatabaseSet()
     *
     * @return \Codeception\Maybe
     */
    public function enableDatabaseSet($set_name)
    {
        $this->scenario->addStep(new \Codeception\Step\Action('enableDatabaseSet', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Like enableDatabaseSet but will always use a freshly built db.
     *
     * @param string $set_name
     *
     * @see Codeception\Module\DpControlHelper::enableFreshDatabaseSet()
     *
     * @return \Codeception\Maybe
     */
    public function enableFreshDatabaseSet($set_name)
    {
        $this->scenario->addStep(new \Codeception\Step\Action('enableFreshDatabaseSet', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Like enableDatabaseSet except this will reset the database set afterwards.
     *
     * @param string $set_name
     * @param bool   $reset    True to mark the db for reset
     *
     * @see Codeception\Module\DpControlHelper::enableDestructiveDatabaseSet()
     *
     * @return \Codeception\Maybe
     */
    public function enableDestructiveDatabaseSet($set_name, $reset = null)
    {
        $this->scenario->addStep(new \Codeception\Step\Action('enableDestructiveDatabaseSet', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Returns the currently set database back to the default.
     *
     * @see Codeception\Module\DpControlHelper::useDefaultDatabase()
     *
     * @return \Codeception\Maybe
     */
    public function useDefaultDatabase()
    {
        $this->scenario->addStep(new \Codeception\Step\Action('useDefaultDatabase', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * @return int
     *
     * @see Codeception\Module\DpControlHelper::getContainerCounter()
     *
     * @return \Codeception\Maybe
     */
    public function getContainerCounter()
    {
        $this->scenario->addStep(new \Codeception\Step\Action('getContainerCounter', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Loads fixtures into the current database.
     * Note that this will mark the database to be reset.
     *
     * @param array $f...
     *
     * @throws \InvalidArgumentException
     *
     * @see Codeception\Module\DpControlHelper::loadFixtures()
     *
     * @return \Codeception\Maybe
     */
    public function loadFixtures($f)
    {
        $this->scenario->addStep(new \Codeception\Step\Action('loadFixtures', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     *
     * @see Codeception\Module\DpControlHelper::indexElasticsearch()
     *
     * @return \Codeception\Maybe
     */
    public function indexElasticsearch()
    {
        $this->scenario->addStep(new \Codeception\Step\Action('indexElasticsearch', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     *
     * @see Codeception\Module::getName()
     *
     * @return \Codeception\Maybe
     */
    public function getName()
    {
        $this->scenario->addStep(new \Codeception\Step\Action('getName', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Enters a directory In local filesystem.
     * Project root directory is used by default
     *
     * @param $path
     *
     * @see Codeception\Module\Filesystem::amInPath()
     *
     * @return \Codeception\Maybe
     */
    public function amInPath($path)
    {
        $this->scenario->addStep(new \Codeception\Step\Condition('amInPath', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Opens a file and stores it's content.
     *
     * Usage:
     *
     * ``` php
     * <?php
     * $I->openFile('composer.json');
     * $I->seeInThisFile('codeception/codeception');
     * ?>
     * ```
     *
     * @param $filename
     *
     * @see Codeception\Module\Filesystem::openFile()
     *
     * @return \Codeception\Maybe
     */
    public function openFile($filename)
    {
        $this->scenario->addStep(new \Codeception\Step\Action('openFile', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Deletes a file
     *
     * ``` php
     * <?php
     * $I->deleteFile('composer.lock');
     * ?>
     * ```
     *
     * @param $filename
     *
     * @see Codeception\Module\Filesystem::deleteFile()
     *
     * @return \Codeception\Maybe
     */
    public function deleteFile($filename)
    {
        $this->scenario->addStep(new \Codeception\Step\Action('deleteFile', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Deletes directory with all subdirectories
     *
     * ``` php
     * <?php
     * $I->deleteDir('vendor');
     * ?>
     * ```
     *
     * @param $dirname
     *
     * @see Codeception\Module\Filesystem::deleteDir()
     *
     * @return \Codeception\Maybe
     */
    public function deleteDir($dirname)
    {
        $this->scenario->addStep(new \Codeception\Step\Action('deleteDir', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Copies directory with all contents
     *
     * ``` php
     * <?php
     * $I->copyDir('vendor','old_vendor');
     * ?>
     * ```
     *
     * @param $src
     * @param $dst
     *
     * @see Codeception\Module\Filesystem::copyDir()
     *
     * @return \Codeception\Maybe
     */
    public function copyDir($src, $dst)
    {
        $this->scenario->addStep(new \Codeception\Step\Action('copyDir', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Checks If opened file has `text` in it.
     *
     * Usage:
     *
     * ``` php
     * <?php
     * $I->openFile('composer.json');
     * $I->seeInThisFile('codeception/codeception');
     * ?>
     * ```
     *
     * @param $text
     * Conditional Assertion: Test won't be stopped on fail
     *
     * @see Codeception\Module\Filesystem::seeInThisFile()
     *
     * @return \Codeception\Maybe
     */
    public function canSeeInThisFile($text)
    {
        $this->scenario->addStep(new \Codeception\Step\ConditionalAssertion('seeInThisFile', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }
    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Checks If opened file has `text` in it.
     *
     * Usage:
     *
     * ``` php
     * <?php
     * $I->openFile('composer.json');
     * $I->seeInThisFile('codeception/codeception');
     * ?>
     * ```
     *
     * @param $text
     *
     * @see Codeception\Module\Filesystem::seeInThisFile()
     *
     * @return \Codeception\Maybe
     */
    public function seeInThisFile($text)
    {
        $this->scenario->addStep(new \Codeception\Step\Assertion('seeInThisFile', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Checks the strict matching of file contents.
     * Unlike `seeInThisFile` will fail if file has something more then expected lines.
     * Better to use with HEREDOC strings.
     * Matching is done after removing "\r" chars from file content.
     *
     * ``` php
     * <?php
     * $I->openFile('process.pid');
     * $I->seeFileContentsEqual('3192');
     * ?>
     * ```
     *
     * @param $text
     * Conditional Assertion: Test won't be stopped on fail
     *
     * @see Codeception\Module\Filesystem::seeFileContentsEqual()
     *
     * @return \Codeception\Maybe
     */
    public function canSeeFileContentsEqual($text)
    {
        $this->scenario->addStep(new \Codeception\Step\ConditionalAssertion('seeFileContentsEqual', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }
    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Checks the strict matching of file contents.
     * Unlike `seeInThisFile` will fail if file has something more then expected lines.
     * Better to use with HEREDOC strings.
     * Matching is done after removing "\r" chars from file content.
     *
     * ``` php
     * <?php
     * $I->openFile('process.pid');
     * $I->seeFileContentsEqual('3192');
     * ?>
     * ```
     *
     * @param $text
     *
     * @see Codeception\Module\Filesystem::seeFileContentsEqual()
     *
     * @return \Codeception\Maybe
     */
    public function seeFileContentsEqual($text)
    {
        $this->scenario->addStep(new \Codeception\Step\Assertion('seeFileContentsEqual', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Checks If opened file doesn't contain `text` in it
     *
     * ``` php
     * <?php
     * $I->openFile('composer.json');
     * $I->dontSeeInThisFile('codeception/codeception');
     * ?>
     * ```
     *
     * @param $text
     * Conditional Assertion: Test won't be stopped on fail
     *
     * @see Codeception\Module\Filesystem::dontSeeInThisFile()
     *
     * @return \Codeception\Maybe
     */
    public function cantSeeInThisFile($text)
    {
        $this->scenario->addStep(new \Codeception\Step\ConditionalAssertion('dontSeeInThisFile', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }
    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Checks If opened file doesn't contain `text` in it
     *
     * ``` php
     * <?php
     * $I->openFile('composer.json');
     * $I->dontSeeInThisFile('codeception/codeception');
     * ?>
     * ```
     *
     * @param $text
     *
     * @see Codeception\Module\Filesystem::dontSeeInThisFile()
     *
     * @return \Codeception\Maybe
     */
    public function dontSeeInThisFile($text)
    {
        $this->scenario->addStep(new \Codeception\Step\Assertion('dontSeeInThisFile', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Deletes a file
     *
     * @see Codeception\Module\Filesystem::deleteThisFile()
     *
     * @return \Codeception\Maybe
     */
    public function deleteThisFile()
    {
        $this->scenario->addStep(new \Codeception\Step\Action('deleteThisFile', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Checks if file exists in path.
     * Opens a file when it's exists
     *
     * ``` php
     * <?php
     * $I->seeFileFound('UserModel.php','app/models');
     * ?>
     * ```
     *
     * @param $filename
     * @param string $path
     *                     Conditional Assertion: Test won't be stopped on fail
     *
     * @see Codeception\Module\Filesystem::seeFileFound()
     *
     * @return \Codeception\Maybe
     */
    public function canSeeFileFound($filename, $path = null)
    {
        $this->scenario->addStep(new \Codeception\Step\ConditionalAssertion('seeFileFound', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }
    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Checks if file exists in path.
     * Opens a file when it's exists
     *
     * ``` php
     * <?php
     * $I->seeFileFound('UserModel.php','app/models');
     * ?>
     * ```
     *
     * @param $filename
     * @param string $path
     *
     * @see Codeception\Module\Filesystem::seeFileFound()
     *
     * @return \Codeception\Maybe
     */
    public function seeFileFound($filename, $path = null)
    {
        $this->scenario->addStep(new \Codeception\Step\Assertion('seeFileFound', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------.
     *
     * Erases directory contents
     *
     * ``` php
     * <?php
     * $I->cleanDir('logs');
     * ?>
     * ```
     *
     * @param $dirname
     *
     * @see Codeception\Module\Filesystem::cleanDir()
     *
     * @return \Codeception\Maybe
     */
    public function cleanDir($dirname)
    {
        $this->scenario->addStep(new \Codeception\Step\Action('cleanDir', func_get_args()));
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();

            return new Maybe($result);
        }

        return new Maybe();
    }
}
