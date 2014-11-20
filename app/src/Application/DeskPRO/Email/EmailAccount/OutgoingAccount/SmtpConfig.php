<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Email\EmailAccount\OutgoingAccount;

use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

class SmtpConfig implements AccountConfigInterface
{
    /**
     * @var string
     */
    public $host;

    /**
     * Default SMTP port is 25, secure 465
     * @var int
     */
    public $port = 25;

    /**
     * @var string
     */
    public $user;

    /**
     * @var string
     */
    public $password;

    /**
     * 'ssl' or 'tls'
     * @var null|string
     */
    public $secure_mode = null;

    /**
     * @var string
     */
    public $helo_string = '';

    /**
     * {@inheritDoc}
     */
    public function serializeJsonArray()
    {
        return array(
            'host'        => $this->host,
            'port'        => $this->port,
            'user'        => $this->user,
            'password'    => $this->password,
            'secure_mode' => $this->secure_mode,
            'helo_string' => $this->helo_string,
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function unserializeJsonArray(array $data)
    {
        $obj = new self();
        foreach ($data as $k => $v) {
            $obj->$k = $v;
        }

        return $obj;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return 'smtp';
    }

    ############################################################################
    # Validation Metadata
    ############################################################################

    public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('host', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('port', new Constraints\GreaterThan(array('value' => 1)));
        $metadata->addPropertyConstraint('secure_mode', new Constraints\Choice(array(
            'choices' => array('none', 'ssl', 'tls'),
        )));
    }
}
