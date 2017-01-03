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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Exception;

/**
 * This exception wraps another exception and adds parameters to the message.
 *
 * For instance, if you throw a BadRequestHttpException, you can specify the error
 * code but you cannot also add parameters that the translator could use in the
 * message of that code. With this, you can!
 *
 * throw new WrappedApiErrorException(new BadRequestHttpException(ApiErrors::some_error), array('foo' => 'bar'))
 *
 * Now in the translated message for "some_error", you can use the "foo" parameter.
 */
class WrappedApiErrorException extends \Exception
{
    /**
     * @var \Exception
     */
    protected $e;

    /**
     * @var array
     */
    protected $translator_params;

    public function __construct(\Exception $e, array $translator_params)
    {
        $this->e                 = $e;
        $this->translator_params = $translator_params;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->translator_params;
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->e;
    }
}
