<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Authenticates a user using one of any compatible adapters.
 *
 * Orb\Auth is much like Zend\Auth except for some subtle differences, including the way we
 * gracefully handle remote-login sources (requiring redirect) in the Result object, as well
 * as how the Identity is an object and we are able to build adapters that can fetch additional
 * userinfo from the sources such as email addresses or names.
 */
class Auth
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    public function __construct(EventDispatcher $dispatcher = null)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Run auth on the adapter.
     *
     * The returned Result object is success, failure or requires a redirect.
     * Success results will have an identity object.
     * Results that require redirects you should redirect using the URL you get from the object
     * Failures may be exceptions, check for the FAILURE_EXCEPTION code and the 'exception' message in the messages.
     *
     * @param \Orb\Auth\Adapter\AdapterInterface $adapter
     *
     * @return \Orb\Auth\Result
     */
    public function authenticate(\Orb\Auth\Adapter\AdapterInterface $adapter)
    {
        try {
            $result = $adapter->authenticate();
        } catch (Exception $e) {
            $result = new Result(Result::FAILURE_EXCEPTION, null, [Result::MSG_EXCEPTION => $e]);
        }

        if ($this->dispatcher) {
            $event  = $this->dispatcher->filter(new Event($this, 'orb.auth.result', ['adapter' => $adapter]), $result);
            $result = $event->getReturnValue();
        }

        return $result;
    }
}
