<?php
namespace Codeception\Module;

class DpControlHelper extends \Codeception\Module
{
	/**
	 * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	public function getSymfonyContainer()
	{
		return \DpTestEnv::getContainer();
	}


	/**
	 * Sets the database set to a version of the set. If it already exists,
	 * it will be re-used (not recreated).
	 *
	 * @param string $set_name
	 */
	public function enableDatabaseSet($set_name)
	{
		\DpTestEnv::enableDatabaseSet($set_name);
	}


	/**
	 * Sets the database set to a fresh version of the set (eg it is re-created if it already exists).
	 *
	 * @param string $set_name
	 */
	public function resetDatabaseSet($set_name)
	{
		\DpTestEnv::enableDatabaseSet($set_name, true);
	}


	/**
	 * Returns the currently set database back to the default.
	 */
	public function useDefaultDatabase()
	{
		\DpTestEnv::restoreDefaultDb();
	}
}
