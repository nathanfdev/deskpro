<?php

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
