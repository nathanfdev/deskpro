<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\EmailBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\PropertyChangedListener;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class SendmailSource implements NotifyPropertyChanged
{
    /**
     * Inserted: record is created only. Usually this is so we have an ID
     * in the database. But no automated processes should touch this record.
     */
    const STATUS_INSERTED = 'inserted';

    /**
     * pending: The email is ready to be sent. The queue process should send
     * pending messages.
     */
    const STATUS_PENDING = 'pending';

    /**
     * retry: The email is set to retry. This means the email was already sent
     * or has already failed, but the email is being retried now.
     */
    const STATUS_RETRY = 'retry';

    /**
     * processing: The email is currently processing. This means the record
     * is 'reserved' by some process attempting to send the message.
     */
    const STATUS_PROCESSING = 'processing';

    /**
     * complete: The email has been sent successfully.
     */
    const STATUS_COMPLETE = 'complete';

    /**
     * error: The email failed to send.
     */
    const STATUS_ERROR = 'error';

    /**
     * aborted: The email was cancelled.
     */
    const STATUS_ABORTED = 'aborted';

    /**
     * Indicates a problem while queueing an email with an external service.
     * (Used when ExternalPendingQueue is used in source mapper).
     */
    const ERR_ENQUEUE_FAILED = 'enqueue_failed';

    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $ref = null;

    /**
     * @var \Application\DeskPRO\Entity\Blob
     */
    protected $blob = null;

    /**
     * @var \Application\DeskPRO\Entity\EmailAccount
     */
    protected $email_account = null;

    /**
     * @var string|null
     */
    protected $context_type = '';

    /**
     * @var int|null
     */
    protected $context_id = 0;

    /**
     * @var array|null
     */
    protected $context_info = null;

    /**
     * Just the headers portion of the email.
     *
     * @var string
     */
    protected $headers = '';

    /**
     * @var string
     */
    protected $header_to = '';

    /**
     * @var string
     */
    protected $header_from = '';

    /**
     * @var string
     */
    protected $header_subject = '';

    /**
     * @var string
     */
    protected $from_email = null;

    /**
     * @var array
     */
    protected $to_emails = null;

    /**
     * @var array
     */
    protected $cc_emails = null;

    /**
     * @var array
     */
    protected $bcc_emails = null;

    /**
     * @var string
     */
    protected $status = 'inserted';

    /**
     * @var string
     */
    protected $options = null;

    /**
     * @var \DateTime
     */
    protected $date_status = null;

    /**
     * @var \DateTime
     */
    protected $date_sent = null;

    /**
     * @var \DateTime
     */
    protected $date_next_attempt = null;

    /**
     * When status is error, this is the code that describes the error.
     *
     * @var string
     */
    protected $error_code = null;

    /**
     * @var \Application\DeskPRO\Entity\Blob
     */
    protected $log_blob = null;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * How many times the email has been processed.
     *
     * @var int
     */
    protected $exec_count = 0;

    /**
     * @var int
     */
    protected $num_targets;

    /**
     * @var int
     */
    protected $num_pending;

    /**
     * @var int
     */
    protected $num_complete;

    /**
     * @var int
     */
    protected $num_error;

    /**
     * @var SendmailSourceStatus[]
     */
    protected $statuses;

    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
        $this->setModelField('date_status', new \DateTime());
        $this->num_targets  = 0;
        $this->num_pending  = 0;
        $this->num_complete = 0;
        $this->num_error    = 0;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @return \Application\DeskPRO\Entity\Blob
     */
    public function getBlob()
    {
        return $this->blob;
    }

    /**
     * @param \Application\DeskPRO\Entity\Blob $blob
     */
    public function setBlob($blob)
    {
        $this->setModelField('blob', $blob);
    }

    /**
     * @return \Application\DeskPRO\Entity\EmailAccount
     */
    public function getEmailAccount()
    {
        return $this->email_account;
    }

    /**
     * @param \Application\DeskPRO\Entity\EmailAccount $email_account
     */
    public function setEmailAccount($email_account)
    {
        $this->setModelField('email_account', $email_account);
    }

    /**
     * @return null|string
     */
    public function getContextType()
    {
        return $this->context_type;
    }

    /**
     * @param null|string $context_type
     */
    public function setContextType($context_type)
    {
        $this->setModelField('context_type', $context_type);
    }

    /**
     * @return int|null
     */
    public function getContextId()
    {
        return $this->context_id;
    }

    /**
     * @param int|null $context_id
     */
    public function setContextId($context_id)
    {
        $this->setModelField('context_id', $context_id);
    }

    /**
     * @return array|null
     */
    public function getContextInfo()
    {
        return $this->context_info;
    }

    /**
     * @param array|null $context_info
     */
    public function setContextInfo($context_info)
    {
        $this->setModelField('context_info', $context_info);
    }

    /**
     * @return string
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $headers
     */
    public function setHeaders($headers)
    {
        $this->setModelField('headers', $headers);
    }

    /**
     * @return string
     */
    public function getHeaderTo()
    {
        return $this->header_to;
    }

    /**
     * @param string $header_to
     */
    public function setHeaderTo($header_to)
    {
        $this->setModelField('header_to', $header_to);
    }

    /**
     * @return string
     */
    public function getHeaderFrom()
    {
        return $this->header_from;
    }

    /**
     * @param string $header_from
     */
    public function setHeaderFrom($header_from)
    {
        $this->setModelField('header_from', $header_from);
    }

    /**
     * @return string
     */
    public function getHeaderSubject()
    {
        return $this->header_subject;
    }

    /**
     * @param string $header_subject
     */
    public function setHeaderSubject($header_subject)
    {
        $this->setModelField('header_subject', $header_subject);
    }

    /**
     * @return string
     */
    public function getFromEmail()
    {
        return $this->from_email;
    }

    /**
     * @param string $from_email
     */
    public function setFromEmail($from_email)
    {
        $this->from_email = $from_email;
    }

    /**
     * @return array
     */
    public function getToEmails()
    {
        return $this->to_emails ?: [];
    }

    /**
     * @param array $to_emails
     */
    public function setToEmails(array $to_emails = null)
    {
        if (empty($to_emails)) {
            $to_emails = null;
        }
        $this->to_emails = $to_emails;
    }

    /**
     * @return array
     */
    public function getCcEmails()
    {
        return $this->cc_emails ?: [];
    }

    /**
     * @param array $cc_emails
     */
    public function setCcEmails($cc_emails)
    {
        if (empty($cc_emails)) {
            $cc_emails = null;
        }
        $this->cc_emails = $cc_emails;
    }

    /**
     * @return array
     */
    public function getBccEmails()
    {
        return $this->bcc_emails ?: [];
    }

    /**
     * @param array $bcc_emails
     */
    public function setBccEmails($bcc_emails)
    {
        if (empty($bcc_emails)) {
            $bcc_emails = null;
        }
        $this->bcc_emails = $bcc_emails;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->setModelField('status', $status);
        $this->setModelField('date_status', new \DateTime());

        switch ($status) {
            case self::STATUS_PENDING:
            case self::STATUS_RETRY:
                if (!$this->date_next_attempt) {
                    $this->setModelField('date_next_attempt', new \DateTime());
                }
                break;
            default:
                $this->setModelField('date_next_attempt', null);
        }
    }

    /**
     * @return \DateTime
     */
    public function getDateStatus()
    {
        return $this->date_status;
    }

    /**
     * @param \DateTime $date_status
     */
    public function setDateStatus($date_status)
    {
        $this->setModelField('date_status', $date_status);
    }

    /**
     * @return \DateTime
     */
    public function getDateSent()
    {
        return $this->date_sent;
    }

    /**
     * @param \DateTime $date_sent
     */
    public function setDateSent($date_sent)
    {
        $this->setModelField('date_sent', $date_sent);
    }

    /**
     * @return \DateTime
     */
    public function getDateNextAttempt()
    {
        return $this->date_next_attempt;
    }

    /**
     * @param \DateTime $date_next_attempt
     */
    public function setDateNextAttempt($date_next_attempt)
    {
        $this->setModelField('date_next_attempt', $date_next_attempt);
    }

    /**
     * @return string
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * @param string $error_code
     */
    public function setErrorCode($error_code)
    {
        $this->setModelField('error_code', $error_code);
    }

    /**
     * @return \Application\DeskPRO\Entity\Blob
     */
    public function getLogBlob()
    {
        return $this->log_blob;
    }

    /**
     * @param \Application\DeskPRO\Entity\Blob $log_blob
     */
    public function setLogBlob($log_blob)
    {
        $this->setModelField('log_blob', $log_blob);
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param \DateTime $date_created
     */
    public function setDateCreated($date_created)
    {
        $this->setModelField('date_created', $date_created);
    }

    /**
     * @return int
     */
    public function getExecCount()
    {
        return $this->exec_count;
    }

    /**
     * @param int $exec_count
     */
    public function setExecCount($exec_count)
    {
        $this->setModelField('exec_count', $exec_count);
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return $this->options ?: [];
    }

    /**
     * @param array $options
     */
    public function setAllOptions(array $options = null)
    {
        $this->setModelField('options', $options ?: null);
    }

    /**
     * @param string $k
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getOption($k, $default = null)
    {
        return ($this->options && isset($this->options[$k])) ? $this->options[$k] : $default;
    }

    /**
     * @return SendmailSourceStatus[]
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * @param string $k
     * @param mixed  $v
     */
    public function setOption($k, $v = null)
    {
        $options = $this->options ?: [];

        if ($v === null) {
            if (isset($options[$k])) {
                unset($options[$k]);
            }
        } else {
            $options[$k] = $v;
        }

        if ($options) {
            $this->setModelField('options', $options);
        } else {
            $this->setModelField('options', null);
        }
    }

    public function toArray()
    {
        $data = [];

        $data['id']             = $this->id;
        $data['ref']            = $this->ref;
        $data['header_to']      = $this->header_to;
        $data['header_from']    = $this->header_from;
        $data['header_subject'] = $this->header_subject;
        $data['status']         = $this->status;
        $data['error_code']     = $this->error_code;
        $data['exec_count']     = $this->exec_count;
        $data['options']        = $this->options;
        $data['num_targets']    = $this->num_targets;
        $data['num_pending']    = $this->num_pending;
        $data['num_error']      = $this->num_error;
        $data['num_complete']   = $this->num_complete;

        foreach (['date_created', 'date_status', 'date_sent', 'date_next_attempt'] as $date_field) {
            if ($this->$date_field) {
                $data[$date_field]        = $this->$date_field->format('Y-m-d H:i:s');
                $data["{$date_field}_ts"] = $this->$date_field->getTimestamp();
            } else {
                $data[$date_field]        = null;
                $data["{$date_field}_ts"] = null;
            }
        }

        if ($this->email_account) {
            $data['email_account'] = [
                'id'                => $this->email_account->id,
                'account_type'      => $this->email_account->account_type,
                'address'           => $this->email_account->address,
                'use_email_address' => $this->email_account->getUseEmailAddress(),
            ];
        } else {
            $data['email_account'] = null;
        }

        if ($this->getBlob()) {
            $data['raw_download_url'] = $this->getBlob()->getDownloadUrl(true);
        }
        if ($this->getLogBlob()) {
            $data['log_download_url'] = $this->getLogBlob()->getDownloadUrl(true);
        }

        return $data;
    }

    /**
     * Exports to an array that would match the database row.
     *
     * @return array
     */
    public function toRecordArray()
    {
        static $scalar_fields = [
            'id',
            'ref',
            'context_type',
            'context_id',
            'context_info',
            'headers',
            'header_to',
            'header_from',
            'header_subject',
            'from_email',
            'status',
            'error_code',
            'exec_count',
        ];

        static $simple_array_fields = [
            'to_emails',
            'cc_emails',
            'bcc_emails',
        ];

        static $date_fields = [
            'date_status',
            'date_sent',
            'date_next_attempt',
            'date_created',
        ];

        static $obj_fields = [
            'blob',
            'email_account',
            'log_blob',
        ];

        $data = [];

        foreach ($scalar_fields as $f) {
            $data[$f] = $this->$f;
        }
        foreach ($simple_array_fields as $f) {
            if ($this->$f) {
                $data[$f] = implode(',', $this->$f);
            } else {
                $data[$f] = '';
            }
        }
        foreach ($date_fields as $f) {
            if ($this->$f) {
                $data[$f] = $this->$f->format('Y-m-d H:i:s');
            } else {
                $data[$f] = null;
            }
        }
        foreach ($obj_fields as $f) {
            if ($this->$f) {
                $data[$f.'_id'] = $this->$f->getId();
            } else {
                $data[$f.'_id'] = null;
            }
        }

        if ($this->options) {
            $data['options'] = json_encode($this->options);
        } else {
            $data['options'] = null;
        }

        return $data;
    }

    public function initTargetsCount()
    {
        $count = count($this->getToEmails()) + count($this->getCcEmails()) + count($this->getBccEmails());
        $this->setModelField('num_targets', $count);
        $this->setModelField('num_pending', $count);
    }

    //###########################################################################
    // Doctrine
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->customRepositoryClassName = 'Application\EmailBundle\EntityRepository\SendmailSourceRepository';

        $metadata->addLifecycleCallback('initTargetsCount', 'prePersist');

        $metadata->setPrimaryTable([
            'name'    => 'sendmail_sources',
            'indexes' => [
                'status_idx'       => ['columns' => ['status']],
                'date_created_idx' => ['columns' => ['date_created']],
            ],
            'uniqueConstraints' => [
                'ref_idx' => ['columns' => ['ref']],
            ],
        ]);

        $metadata->mapField([
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'id'         => true,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'ref',
            'fieldName'  => 'ref',
            'type'       => 'string',
            'length'     => 100,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'context_type',
            'fieldName'  => 'context_type',
            'type'       => 'string',
            'length'     => 100,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'context_id',
            'fieldName'  => 'context_id',
            'type'       => 'integer',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'context_info',
            'columnName' => 'context_info',
            'type'       => 'json_array',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'headers',
            'fieldName'  => 'headers',
            'type'       => 'text',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'header_to',
            'fieldName'  => 'header_to',
            'type'       => 'text',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'header_from',
            'fieldName'  => 'header_from',
            'type'       => 'text',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'header_subject',
            'fieldName'  => 'header_subject',
            'type'       => 'text',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'from_email',
            'fieldName'  => 'from_email',
            'type'       => 'text',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'to_emails',
            'fieldName'  => 'to_emails',
            'type'       => 'simple_array',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'cc_emails',
            'fieldName'  => 'cc_emails',
            'type'       => 'simple_array',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'bcc_emails',
            'fieldName'  => 'bcc_emails',
            'type'       => 'simple_array',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'status',
            'fieldName'  => 'status',
            'type'       => 'string',
            'length'     => 80,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'options',
            'fieldName'  => 'options',
            'type'       => 'json_array',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_status',
            'columnName' => 'date_status',
            'type'       => 'datetime',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_sent',
            'columnName' => 'date_sent',
            'type'       => 'datetime',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_next_attempt',
            'columnName' => 'date_next_attempt',
            'type'       => 'datetime',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'error_code',
            'fieldName'  => 'error_code',
            'type'       => 'string',
            'length'     => 80,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'columnName' => 'date_created',
            'type'       => 'datetime',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'exec_count',
            'fieldName'  => 'exec_count',
            'type'       => 'integer',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'num_targets',
            'fieldName'  => 'num_targets',
            'type'       => 'integer',
        ]);
        $metadata->mapField([
            'columnName' => 'num_pending',
            'fieldName'  => 'num_pending',
            'type'       => 'integer',
        ]);
        $metadata->mapField([
            'columnName' => 'num_error',
            'fieldName'  => 'num_error',
            'type'       => 'integer',
        ]);
        $metadata->mapField([
            'columnName' => 'num_complete',
            'fieldName'  => 'num_complete',
            'type'       => 'integer',
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'blob',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Blob',
            'dpApi'        => true,
            'dpApiDeep'    => true,
            'joinColumns'  => [[
                'name'                 => 'blob_id',
                'referencedColumnName' => 'id',
                'nullable'             => true,
                'onDelete'             => 'cascade',
            ]],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'email_account',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\EmailAccount',
            'dpApi'        => true,
            'joinColumns'  => [[
                'name'                 => 'email_account_id',
                'referencedColumnName' => 'id',
                'nullable'             => true,
                'onDelete'             => 'cascade',
            ]],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'log_blob',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Blob',
            'dpApi'        => true,
            'dpApiDeep'    => true,
            'joinColumns'  => [[
                'name'                 => 'log_blob_id',
                'referencedColumnName' => 'id',
                'nullable'             => true,
                'onDelete'             => 'set null',
            ]],
        ]);
        $metadata->mapOneToMany([
            'fieldName'    => 'statuses',
            'targetEntity' => 'Application\EmailBundle\Entity\SendmailSourceStatus',
            'mappedBy'     => 'source',
        ]);
    }

    public function __getPropValue__($k)
    {
        return $this->$k;
    }
    public function __setPropValue__($k, $v)
    {
        $this->$k = $v;
    }
    public function __hasRunLoad__()
    {
        return true;
    }

    /** @var PropertyChangedListener[] */
    private $_listeners = [];
    public function addPropertyChangedListener(PropertyChangedListener $listener)
    {
        $this->setModelField('_listeners[]', $listener);
    }

    private function setModelField($field, $value)
    {
        $old = null;
        if (property_exists($this, $field)) {
            $old = $this->$field;
        }

        // Detect fields that did not change
        if (is_null($value) && is_null($old)) {
            return;
        } elseif (is_scalar($value)) {
            if (is_numeric($value) && is_numeric($old)) {
                if ($value == $old) {
                    return;
                }
            } else {
                if ($value === $old) {
                    return;
                }
            }
        } elseif ($value instanceof \DateTime) {
            if ($old instanceof \DateTime && $value->getTimestamp() == $old->getTimestamp()) {
                return;
            }
        } elseif (is_object($value) && isset($value->id) && is_object($old) && isset($old->id)) {
            if ($value->id == $old->id) {
                return;
            }
        }

        $this->$field = $value;

        foreach ($this->_listeners as $listener) {
            $listener->propertyChanged($this, $field, $old, $value);
        }
    }
}
