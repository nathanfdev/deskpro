<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use DeskPRO\Bundle\AppBundle\Entity\EmailAccountLog;
use DeskPRO\Component\Util\RegexUtils;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Raw email sources.
 */
class EmailSource extends \Application\DeskPRO\Domain\DomainObject
{
    const STATUS_INSERTED      = 'inserted';
    const STATUS_RETRY         = 'retry';
    const STATUS_PROCESSING    = 'processing';
    const STATUS_COMPLETE      = 'complete';
    const STATUS_ERROR         = 'error';
    const STATUS_REJECTED      = 'rejected';
    const STATUS_REJECTED_SOFT = 'rejected_soft';

    const OBJ_TYPE_TICKET         = 'ticket';
    const OBJ_TYPE_TICKET_MESSAGE = 'ticketmessage';

    const ERR_SERVER_ERROR      = 'server_error';
    const ERR_FROM_MISSING      = 'from_missing';
    const ERR_FROM_INVALID      = 'from_invalid';
    const ERR_FROM_GATEWAY      = 'from_gateway_address';
    const ERR_FROM_BANNED       = 'from_banned';
    const ERR_FROM_DISABLED     = 'from_disabled_user';
    const ERR_SUBJECT_MISSING   = 'subject_missing';
    const ERR_MESSAGE_EMPTY     = 'message_missing';
    const ERR_MESSAGE_TOO_BIG   = 'message_too_big';
    const ERR_EMPTY             = 'empty';
    const ERR_DUPE              = 'duplicate_message';
    const ERR_AUTORESPONDER     = 'autoresponder';
    const ERR_SPAM              = 'spam';
    const ERR_REQUIRE_REG       = 'require_reg';
    const ERR_OBJ_CLOSED        = 'obj_closed';
    const ERR_OBJ_DELETED       = 'obj_deleted';
    const ERR_OBJ_UNKNOWN       = 'obj_unknown';
    const ERR_AUTH_INVALID      = 'auth_invalid';
    const ERR_AUTH_MISSING      = 'auth_missing';
    const ERR_DESKPRO_EMAIL     = 'deskpro_email';
    const ERR_PERM_INSUFFICIENT = 'perm_insufficient';
    const ERR_INVALID_FWD       = 'invalid_fwd';
    const ERR_INVALID_FWD_EMAIL = 'invalid_fwd_email';
    const ERR_MISSING_MARKER    = 'missing_marker';
    const ERR_AGENT_BOUNCE      = 'agent_bounce';
    const ERR_DATE_LIMIT        = 'date_limit';
    const ERR_INVALID_ADDRESS   = 'invalid_address';
    const ERR_RATE_LIMIT        = 'rate_limit';
    const ERR_USER_VALIDATING   = 'user_validating';
    const ERR_SPF_REJECT        = 'spf_rejected';
    const ERR_DKIM_REJECT       = 'dkim_rejected';

    /**
     * @var int
     */
    protected $id = null;

    /**
     * The message UID.
     *
     * @var null
     */
    protected $uid = null;

    /**
     * @var \Application\DeskPRO\Entity\Blob
     */
    protected $blob = null;

    /**
     * @var \Application\DeskPRO\Entity\EmailAccount
     */
    protected $email_account = null;

    /**
     * The type of object this is attached to (should be the table name of
     * the super type, eg: tickets, people, organizations).
     *
     * This typically is not set until after the email is processed (ie
     * the status is 'processed').
     *
     * @var string
     */
    protected $object_type = '';

    /**
     * The ID of the object this is attached to.
     *
     * @var int
     */
    protected $object_id = '';

    /**
     * @var array|null
     */
    protected $object_info = null;

    /**
     * @var string
     */
    protected $from_email = '';

    /**
     * Just the headers portion of the email.
     *
     * @var string
     */
    protected $headers;

    /**
     * @var string
     */
    protected $header_to = '';

    /**
     * @var string
     */
    protected $header_cc = '';

    /**
     * @var string
     */
    protected $header_from = '';

    /**
     * @var string
     */
    protected $header_subject = '';

    /**
     * @var array
     */
    protected $parsed_headers = [];

    /**
     * The current status of the message:
     * - inserted: Only inserted
     * - processing: Currently processing
     * - complete: Fully processed
     * - error: Tried to process but there was some kind of error (see error_code).
     *
     * @var string
     */
    protected $status = 'processing';

    /**
     * @var \DateTime
     */
    protected $date_status = null;

    /**
     * When status is error, this is the code that describes the error.
     *
     * @var string
     */
    protected $error_code = null;

    /**
     * A string of other info/debug info about the source. For example, if there is an error then additional
     * information might be placed here.
     *
     * @var string
     */
    protected $source_info = null;

    /**
     * @var \Application\DeskPRO\Entity\Blob
     */
    protected $log_blob = null;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Entity\EmailAccountLog
     */
    protected $email_account_log;

    /**
     * How many times the email has been processed.
     *
     * @var int
     */
    protected $exec_count = 0;

    /**
     * The raw source, pieced together.
     *
     * This is public on purpose. The AbstractFetcher
     * sets this property for efficiency in cases where an email
     * may be processed immediately after being read, we dont
     * re-fetch the data from the db.
     *
     * @var string
     */
    public $_raw = null;

    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
        $this->setModelField('date_status', new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return EmailAccount
     */
    public function getEmailAccount()
    {
        return $this->email_account;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return Blob
     */
    public function getBlob()
    {
        return $this->blob;
    }

    /**
     * Get the full raw source of the email.
     *
     * @deprecated
     *
     * @return string
     */
    public function getRawSource()
    {
        if ($this->_raw !== null) {
            return $this->_raw;
        }

        $this->_raw = App::getContainer()->getBlobStorage()->copyBlobRecordToString($this->blob);

        return $this->_raw;
    }

    /**
     * @return string
     */
    public function getSourceInfo()
    {
        return $this->source_info;
    }

    /**
     * @return Blob
     */
    public function getLogBlob()
    {
        return $this->log_blob;
    }

    /**
     * @return string
     */
    public function getSourceInfoAsString()
    {
        if (!$this->source_info) {
            return '';
        }

        if (isset($this->source_info[0])) {
            return implode("\n", $this->source_info);
        } else {
            return print_r($this->source_info, true);
        }
    }

    /**
     * Clears local cache of raw source.
     */
    public function clearRawSource()
    {
        $this->_raw = null;
    }

    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * @return string
     */
    public function getErrorCodeTitle()
    {
        if (!$this->error_code) {
            return '';
        }

        switch ($this->error_code) {
            case self::ERR_SERVER_ERROR:        return 'Server Error';
            case self::ERR_FROM_MISSING:        return 'Missing From Address';
            case self::ERR_FROM_INVALID:        return 'Invalid From Address';
            case self::ERR_FROM_GATEWAY:        return 'From Gateway Address';
            case self::ERR_FROM_BANNED:         return 'Banned From Addres';
            case self::ERR_FROM_DISABLED:       return 'From Disabled User';
            case self::ERR_SUBJECT_MISSING:     return 'Subject Missing';
            case self::ERR_MESSAGE_EMPTY:       return 'Empty message';
            case self::ERR_MESSAGE_TOO_BIG:     return 'Message Too Big';
            case self::ERR_EMPTY:               return 'Empty Source';
            case self::ERR_DUPE:                return 'Duplicate';
            case self::ERR_AUTORESPONDER:       return 'Auto-repsonse';
            case self::ERR_SPAM:                return 'Spam';
            case self::ERR_REQUIRE_REG:         return 'User Requires Registration';
            case self::ERR_OBJ_CLOSED:          return 'Ticket Archived';
            case self::ERR_OBJ_DELETED:         return 'Ticket Deleted';
            case self::ERR_OBJ_UNKNOWN:         return 'Unknown Ticket';
            case self::ERR_AUTH_INVALID:        return 'Invalid Auth Code';
            case self::ERR_AUTH_MISSING:        return 'Missing Auth Code';
            case self::ERR_DESKPRO_EMAIL:       return 'DeskPRO Address';
            case self::ERR_PERM_INSUFFICIENT:   return 'Insufficient Permissions';
            case self::ERR_INVALID_FWD:         return 'Invalid Forward: Could not parse';
            case self::ERR_INVALID_FWD_EMAIL:   return 'Invalid Forward: Invalid user email address';
            case self::ERR_MISSING_MARKER:      return 'Missing Marker';
            case self::ERR_RATE_LIMIT:          return 'Rate Limited';
        }

        return $this->error_code;
    }

    /**
     * @param array $info
     */
    public function setObjectInfo(array $info = null)
    {
        if (!$info) {
            $this->setModelField('object_info', null);
        } else {
            $this->setModelField('object_info', $info);
        }
    }

    /**
     * @return array
     */
    public function getObjectInfo()
    {
        return $this->object_info ? $this->object_info : [];
    }

    /**
     * @param string $k
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getObjectInfoKey($k, $default = null)
    {
        return isset($this->object_id[$k]) ? $this->object_id[$k] : $default;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->setModelField('status', $status);
        $this->setModelField('date_status', new \DateTime());
    }

    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);
        if (!$deep) {
            unset($data['source_info']);
        }

        $data['object_info'] = $this->object_info;

        // EmailAccountLog is a new-style entity, doesnt use toApiData
        if ($this->email_account_log) {
            $data['email_account_log'] = [
                'id'           => $this->email_account_log->getId(),
                'blob'         => $this->email_account_log->getBlob() ? $this->email_account_log->getBlob()->toApiData(false, $deep) : null,
                'protocol'     => $this->email_account_log->getProtocol(),
                'num_emails'   => $this->email_account_log->getNumEmails(),
                'date_created' => $this->email_account_log->getDateCreated()->format('Y-m-d H:i:s'),
            ];
        } else {
            $data['email_account_log'] = null;
        }

        return $data;
    }

    public function getParsedHeaders()
    {
        if ($this->parsed_headers || !strlen($this->headers)) {
            return $this->parsed_headers;
        }

        $headers = $this->headers;
        if (false !== $pos = strpos($this->headers, "\r\n\r\n")) {
            $headers = substr($this->headers, 0, $pos);
        }

        $current = null;
        $headers = explode("\n", $headers);
        foreach ($headers as $str) {
            if (empty($str)) {
                continue;
            }
            if (RegexUtils::safePregMatch('/^[A-Za-z]/', $str[0])) {
                $parts                         = explode(':', $str);
                $header                        = strtolower($parts[0]);
                $this->parsed_headers[$header] = trim($parts[1]);
                $current                       = $header;
            } elseif ($current) {
                $this->parsed_headers[$current] .= substr($str, 1);
            }
        }

        return $this->parsed_headers;
    }

    /**
     * @param EmailAccountLog $emailAccountLog
     */
    public function setEmailAccountLog(EmailAccountLog $emailAccountLog)
    {
        $this->setModelField('email_account_log', $emailAccountLog);
    }

    /**
     * @return EmailAccountLog
     */
    public function getEmailAccountLog()
    {
        return $this->email_account_log;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\\DeskPRO\\EntityRepository\\EmailSource';
        $metadata->setPrimaryTable([
            'name'    => 'email_sources',
            'indexes' => [
                'date_created' => ['columns' => ['date_created']],
                'object_idx'   => [
                    'columns' => [
                        'object_type',
                        'object_id',
                    ],
                ],
                'status_idx' => ['columns' => ['status']],
                'from_idx'   => ['columns' => ['from_email']],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'uid',
            'type'       => 'string',
            'length'     => 100,
            'nullable'   => true,
            'columnName' => 'uid',
        ]);
        $metadata->mapField([
            'fieldName'  => 'object_type',
            'type'       => 'string',
            'length'     => 50,
            'nullable'   => false,
            'columnName' => 'object_type',
        ]);
        $metadata->mapField([
            'fieldName'  => 'object_id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'object_id',
        ]);
        $metadata->mapField([
            'columnName' => 'object_info',
            'fieldName'  => 'object_info',
            'type'       => 'json_array',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'from_email',
            'type'       => 'string',
            'length'     => 255,
            'columnName' => 'from_email',
        ]);
        $metadata->mapField([
            'fieldName'  => 'headers',
            'type'       => 'text',
            'nullable'   => false,
            'columnName' => 'headers',
        ]);
        $metadata->mapField([
            'fieldName'  => 'header_to',
            'type'       => 'text',
            'nullable'   => false,
            'columnName' => 'header_to',
        ]);
        $metadata->mapField([
            'fieldName'  => 'header_cc',
            'type'       => 'text',
            'nullable'   => false,
            'columnName' => 'header_cc',
        ]);
        $metadata->mapField([
            'fieldName'  => 'header_from',
            'type'       => 'text',
            'nullable'   => false,
            'columnName' => 'header_from',
        ]);
        $metadata->mapField([
            'fieldName'  => 'header_subject',
            'type'       => 'text',
            'nullable'   => false,
            'columnName' => 'header_subject',
        ]);
        $metadata->mapField([
            'fieldName'  => 'status',
            'type'       => 'string',
            'length'     => 15,
            'nullable'   => false,
            'columnName' => 'status',
        ]);
        $metadata->mapField([
            'fieldName'  => 'exec_count',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'exec_count',
        ]);
        $metadata->mapField([
            'fieldName'  => 'error_code',
            'type'       => 'string',
            'length'     => 80,
            'nullable'   => true,
            'columnName' => 'error_code',
        ]);
        $metadata->mapField([
            'fieldName'  => 'source_info',
            'type'       => 'array',
            'nullable'   => true,
            'columnName' => 'source_info',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_status',
            'type'       => 'datetime',
            'nullable'   => false,
            'columnName' => 'date_status',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'blob',
            'targetEntity' => Blob::class,
            'dpApi'        => true,
            'dpApiDeep'    => true,
            'joinColumns'  => [
                [
                    'name'                 => 'blob_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'email_account',
            'targetEntity' => EmailAccount::class,
            'dpApi'        => true,
            'joinColumns'  => [
                [
                    'name'                 => 'email_account_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'log_blob',
            'targetEntity' => Blob::class,
            'dpApi'        => true,
            'dpApiDeep'    => true,
            'joinColumns'  => [
                [
                    'name'                 => 'log_blob_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'email_account_log',
            'targetEntity' => EmailAccountLog::class,
            'dpApi'        => true,
            'dpApiDeep'    => true,
            'joinColumns'  => [
                [
                    'name'                 => 'email_account_log_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                ],
            ],
        ]);
    }
}
