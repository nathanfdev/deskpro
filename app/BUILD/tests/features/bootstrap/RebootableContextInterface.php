<?php

/**
 * DeskPRO.
 */

namespace DpBehat;

/**
 * If your context requires something from the Symfony container, you should almost certainly
 * implement this method to re-fetch/re-set the context.
 *
 * When you `@reinstall` something, the database is re-installed and the kernel+container rebooted;
 * you need to manualy reboot your own contexts if they have dependencies.
 *
 * Generally speaking, you DONT want to use behat.yml to configure deps. Use a rebootContext.
 *
 * The exception to the rule is if you are sure that the dep isnt going to change between reinstall calls.
 * That is, the dep is static during the life of the feature.
 */
interface RebootableContextInterface
{
    /**
     * This method will be called:.
     *
     * - as soon as the kernel is set (so you can access kernel and container)
     * - any time the db has been re-installed.
     *
     * Generally speaking, you can use this is a kind of init/constructor, setting the proper
     * pristine state on the context.
     */
    public function rebootContext();
}
