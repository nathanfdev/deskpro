<?php

/**
 * DeskPRO.
 *
 * @category Usersources
 */

namespace Orb\HttpFoundation\Session;

/**
 * This is a session utility that helps create flash messages for a user. These are messages that appear
 * once and then disappear. For example, a success message at the top of a page could be added during a redirect
 * and then displayed on the next real page load.
 */
class FlashMessenger
{
    /**
     * @var Orb\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * Messages we read from the session for the current request. They
     * will be deleted now.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * The messages that we'll save for the next request.
     *
     * @var ArrayObject
     */
    protected $current_messages;

    public function __construct(SessionInterface $session)
    {
        $this->session  = $session;
        $this->messages = $session->get('flash_messages')->getArrayCopy();

        $this->current_messages = new \ArrayObject();
        $session->set('flash_messages', $this->current_messages);
    }

    /**
     * Get the messages for this request.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Gets the messages added during this request, but won't be displayed until the next.
     *
     * @return array
     */
    public function getCurrentMessages()
    {
        return $this->current_messages->getArrayCopy();
    }

    /**
     * Get messages for this request, as well as messages we just added.
     *
     * @return array
     */
    public function getAllMessages()
    {
        return array_merge($this->messages, $this->current_messages->getArrayCopy());
    }

    /**
     * Remove the messages for this request.
     */
    public function clearMessages()
    {
        $this->messages = [];
    }

    /**
     * Remove the messages we added during this request.
     */
    public function clearCurrentMessages()
    {
        $this->current_messages->exchangeArray([]);
    }

    /**
     * Clear all messages, both from session and current.
     */
    public function clearAllMessages()
    {
        $this->clearMessages();
        $this->clearCurrentMessages();
    }

    /**
     * Add a message for the next request.
     *
     * @param  $message
     */
    public function addMessage($message)
    {
        $this->session->get('flash_messages')->append($message);
    }
}
