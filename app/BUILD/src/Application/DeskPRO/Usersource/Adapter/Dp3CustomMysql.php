<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

class Dp3CustomMysql extends DbTablePhpPasswordCheck
{
    /**
     * @return \Orb\Auth\Adapter\DbTablePhpPasswordCheck
     */
    protected function _createAuthAdapterObject()
    {
        $options = $this->usersource->options;

        // Bit of adapter code to convert Dp3 eval code format into the new one
        $options['password_php'] = '
            $password_check = $input = $password_input;
            '.$options['password_php'].'
            $pass = ($password_check == $userinfo_password);
        ';

        return new \Orb\Auth\Adapter\DbTablePhpPasswordCheck($this->getDb(), $options);
    }
}
