<?php

namespace DeskPRO\Build\Upgrade;

use Symfony\Component\Console\Output\OutputInterface;

use DeskPRO\Build\Upgrade\Upgrader;

/**
 * An upgrader must be named:
 * <var>UpgradeVERSION</var>
 *
 * Where version is a version ID. Example:
 * <var>Upgrade20101126122900</var>
 */
abstract class UpgradeAbstract
{
	protected $output;

	public function __construct(OutputInterface $output)
	{
		$this->output = $output;
	}

	/*
	 * Here is an example upgrade step:
	 *
	 * public function step1($sub_step)
	 * {
	 *     // do some work, output status
	 *     $this->output->write("Processing 1234 users ... Batch $sub_step");
	 *
	 *     // Handle errors gracefully
	 *     try {
	 *         // ...
	 *     } catch (Exception $e) {
	 *         return Upgrader::STEP_FAILED;
	 *     }
	 *
	 *     if ($more_work_to_do) {
	 *         return Upgrader::STEP_AGAIN;
	 *     }
	 *
	 *     return Upgrader::STEP_DONE;
	 * }
	 */
}