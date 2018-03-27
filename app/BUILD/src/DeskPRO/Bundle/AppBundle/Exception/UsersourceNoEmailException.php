<?php

namespace DeskPRO\Bundle\AppBundle\Exception;

/**
 * Thrown by the LoginProcessor if a usersource tries to authenticate a new Peron but there is no email.
 */
class UsersourceNoEmailException extends \RuntimeException
{
}
