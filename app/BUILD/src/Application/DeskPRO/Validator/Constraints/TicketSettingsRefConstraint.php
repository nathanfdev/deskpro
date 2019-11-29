<?php

namespace Application\DeskPRO\Validator\Constraints;

use Application\DeskPRO\RefGenerator\CustomRef;
use Symfony\Component\Validator\Constraint;

/**
 * Class TicketSettingsRefConstraint
 *
 * @package Application\DeskPRO\Validator\Constraints
 */
class TicketSettingsRefConstraint extends Constraint
{
    const TYPE_TOKEN = 'token';
    const TYPE_CHAR  = 'character';

    /**
     * @var string
     */
    public $message = 'Invalid reference {{ type }}, "{{ pattern }}" is not allowed.';

    /**
     * @var string
     */
    public $invalidCharCode = 'invalid_reference_char';

    /**
     * @var string
     */
    public $invalidTokenCode = 'invalid_reference_token';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'ticket_settings_validator';
    }

    /**
     * @return string
     */
    public function getInvalidTokenRegexp()
    {
        $tokens = implode('|', array_keys(CustomRef::$keywords));
        $tokens = addcslashes($tokens, '?.-');

        return "/<(?!({$tokens}))\w+>/";
    }

    /**
     * @return string
     */
    public function getInvalidCharRegexp()
    {
        $tokens = array_filter(array_keys(CustomRef::$keywords),
            function ($token) {
                return preg_match('/[^A-Z]/', $token);
            });
        $tokens = addcslashes(implode('', $tokens), '?.-');

        return "/[^\w\-\.<>{$tokens}]+/";
    }
}
