<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Serializer\Deferred;

/**
 * If a property is transformed to this class, we will run the callback and use its returned data as the
 * property value. We execute the callback after transformation in a later event.
 */
class CallbackDeferredProperty
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * @var array
     */
    private $arguments;

    /**
     * Constructor.
     *
     * @param callable $callable
     * @param array    $arguments
     */
    public function __construct(callable $callable, array $arguments = [])
    {
        $this->callable  = $callable;
        $this->arguments = $arguments;
    }

    /**
     * @return mixed
     */
    public function call()
    {
        return call_user_func_array($this->callable, $this->arguments);
    }
}
