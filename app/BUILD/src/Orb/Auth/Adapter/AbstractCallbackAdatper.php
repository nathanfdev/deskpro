<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

use Orb\Auth\StateHandler\StateHandlerInterface;
use Orb\Log\Loggable;
use Orb\Log\Logger;

/**
 * A shell abstract adapter useful for all types that follow the two(or more)-step process of redirecting
 * the user offsite and back.
 */
abstract class AbstractCallbackAdatper extends PluginAdapter implements SessionStateInterface, CallbackInterface, Loggable
{
    const DISLPAY_CONTEXT_PAGE  = 'page';
    const DISLPAY_CONTEXT_POPUP = 'popup';

    /**
     * If in callback context, then an array of callback data.
     *
     * @var array
     */
    protected $callback_data = null;

    /**
     * State handler to store session data.
     *
     * @var \Orb\Auth\StateHandler\StateHandlerInterface;
     */
    protected $state;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * The callback URL.
     *
     * @var string
     */
    protected $callback_url = null;

    /**
     * @var null
     */
    protected $display_context = null;

    /**
     * Switches the adapter to the callback context using form data $data.
     *
     * @param array $data Form data or other callback data
     */
    public function setCallbackContext(array $data)
    {
        $this->callback_data = $data;
    }

    /**
     * Set the URL the user is returned to.
     *
     * @param string $url
     */
    public function setCallbackUrl($url)
    {
        $this->callback_url = $url;
    }

    /**
     * Get the callback URL.
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getCallbackUrl()
    {
        if (!$this->callback_url) {
            throw new \RuntimeException('No callback URL was set');
        }

        return $this->callback_url;
    }

    /**
     * Are we currently in callback mode?
     *
     * @return bool
     */
    public function isCallbackMode()
    {
        return $this->callback_data !== null;
    }

    /**
     * Authenticate a user.
     *
     * @return \Orb\Auth\Result
     */
    public function doAuthenticate()
    {
        if ($this->logger) {
            $this->logger->log('START '.get_class($this).'::authenticate', Logger::DEBUG);
        }

        if ($this->isCallbackMode()) {
            if ($this->logger) {
                $this->logger->log('Entering Callback Mode: '.get_class($this).'::authenticateCallback', Logger::DEBUG);
            }

            return $this->authenticateCallback($this->callback_data, $this->getStateHandler());
        } else {
            if ($this->logger) {
                $this->logger->log(
                    'Entering Initialize Mode: '.get_class($this).'::authenticateInitialize', Logger::DEBUG
                );
            }

            return $this->authenticateInitialize($this->getStateHandler());
        }
    }

    /**
     * Process the callback and return a final result.
     *
     *
     * @param array                 $callback_data
     * @param StateHandlerInterface $state
     *
     * @return \Orb\Auth\Result
     */
    abstract protected function authenticateCallback(array $callback_data, StateHandlerInterface $state);

    /**
     * Initialize the auth process by setting state, and returning a redirect result.
     *
     * @param StateHandlerInterface $state
     *
     * @return \Orb\Auth\Result
     */
    abstract protected function authenticateInitialize(StateHandlerInterface $state);

    /**
     * Set the state handler.
     *
     * @param \Orb\Auth\StateHandler\StateHandlerInterface $state The state handler
     */
    public function setStateHandler(StateHandlerInterface $state)
    {
        $this->state = $state;
    }

    /**
     * Get the state handler.
     *
     * @return \Orb\Auth\StateHandler\StateHandlerInterface
     */
    public function getStateHandler()
    {
        if (!$this->state) {
            throw new \RuntimeException('No state handler was set. Set one with setStateHandler');
        }

        return $this->state;
    }

    /**
     * Set the logger.
     *
     * @param \Orb\Log\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Orb\Log\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
