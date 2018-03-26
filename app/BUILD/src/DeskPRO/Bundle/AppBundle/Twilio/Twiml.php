<?php

namespace DeskPRO\Bundle\AppBundle\Twilio;

/**
 * Class Twiml.
 *
 * @method $this say($message, array $options = [])
 * @method $this pause(array $options = [])
 * @method $this gather(array $options)
 * @method $this play($url, array $options = [])
 * @method $this record(array $options)
 * @method $this dial(array $options = [])
 * @method $this conference($name, array $options = [])
 * @method $this hangup()
 * @method $this enqueue(array $options)
 * @method $this task($jsonOptions)
 */
class Twiml extends \Twilio\Twiml
{
}
