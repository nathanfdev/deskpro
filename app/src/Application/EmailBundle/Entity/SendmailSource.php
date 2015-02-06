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
    const STATUS_INSERTED   = 'inserted';

    /**
     * pending: The email is ready to be sent. The queue process should send
     * pending messages.
     */
    const STATUS_PENDING    = 'pending';

    /**
     * retry: The email is set to retry. This means the email was already sent
     * or has already failed, but the email is being retried now.
     */
    const STATUS_RETRY      = 'retry';

    /**
     * processing: The email is currently processing. This means the record
     * is 'reserved' by some process attempting to send the message.
     */
    const STATUS_PROCESSING = 'processing';

    /**
     * complete: The email has been sent successfully.
     */
    const STATUS_COMPLETE   = 'complete';

    /**
     * error: The email failed to send.
     */
    const STATUS_ERROR      = 'error';

    /**
     * aborted: The email was cancelled.
     */
    const STATUS_ABORTED    = 'aborted';

    /**
     * @var int
     *
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
     * Just the headers portion of the email
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
     * @var int
     */
    protected $exec_count = 0;

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
        return $this->to_emails ?: array();
    }

    /**
     * @param array $to_emails
     */
    public function setToEmails(array $to_emails = null)
    {
        if (empty($to_emails)) $to_emails = null;
        $this->to_emails = $to_emails;
    }

    /**
     * @return array
     */
    public function getCcEmails()
    {
        return $this->cc_emails ?: array();
    }

    /**
     * @param array $cc_emails
     */
    public function setCcEmails($cc_emails)
    {
        if (empty($cc_emails)) $cc_emails = null;
        $this->cc_emails = $cc_emails;
    }

    /**
     * @return array
     */
    public function getBccEmails()
    {
        return $this->bcc_emails ?: array();
    }

    /**
     * @param array $bcc_emails
     */
    public function setBccEmails($bcc_emails)
    {
        if (empty($bcc_emails)) $bcc_emails = null;
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

    public function toArray()
    {
        $data = array();

        $data['id'] = $this->id;
        $data['ref'] = $this->ref;
        $data['header_to'] = $this->header_to;
        $data['header_from'] = $this->header_from;
        $data['header_subject'] = $this->header_subject;
        $data['status'] = $this->status;
        $data['error_code'] = $this->error_code;
        $data['exec_count'] = $this->exec_count;

        foreach (array('date_created', 'date_status', 'date_sent', 'date_next_attempt') as $date_field) {
            if ($this->$date_field) {
                $data[$date_field] = $this->$date_field->format('Y-m-d H:i:s');
                $data["{$date_field}_ts"] = $this->$date_field->getTimestamp();
            } else {
                $data[$date_field] = null;
                $data["{$date_field}_ts"] = null;
            }
        }

        if ($this->email_account) {
            $data['email_account'] = array(
                'id'                => $this->email_account->id,
                'account_type'      => $this->email_account->account_type,
                'address'           => $this->email_account->address,
                'use_email_address' => $this->email_account->getUseEmailAddress(),
            );
        } else {
            $data['email_account'] = null;
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
        static $scalar_fields = array(
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
        );

        static $simple_array_fields = array(
            'to_emails',
            'cc_emails',
            'bcc_emails',
        );

        static $date_fields = array(
            'date_status',
            'date_sent',
            'date_next_attempt',
            'date_created',
        );

        static $obj_fields = array(
            'blob',
            'email_account',
            'log_blob',
        );

        $data = array();

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

        return $data;
    }

    ############################################################################
    # Doctrine
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->customRepositoryClassName = 'Application\EmailBundle\EntityRepository\SendmailSourceRepository';
        $metadata->setPrimaryTable(array(
            'name' => 'sendmail_sources',
            'indexes' => array(
                'status_idx'       => array('columns' => array('status')),
                'date_created_idx' => array('columns' => array('date_created')),
            ),
            'uniqueConstraints' => array(
                'ref_idx' => array('columns' => array('ref'))
            )
        ));

        $metadata->mapField(array(
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'id'         => true,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'ref',
            'fieldName'  => 'ref',
            'type'       => 'string',
            'length'     => 100,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'context_type',
            'fieldName'  => 'context_type',
            'type'       => 'string',
            'length'     => 100,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'context_id',
            'fieldName'  => 'context_id',
            'type'       => 'integer',
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'fieldName'  => 'context_info',
            'columnName' => 'context_info',
            'type'       => 'json_array',
            'nullable'   => true,
        ));
        $metadata->mapField(array(
            'columnName' => 'headers',
            'fieldName'  => 'headers',
            'type'       => 'text',
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'header_to',
            'fieldName'  => 'header_to',
            'type'       => 'text',
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'header_from',
            'fieldName'  => 'header_from',
            'type'       => 'text',
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'header_subject',
            'fieldName'  => 'header_subject',
            'type'       => 'text',
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'from_email',
            'fieldName'  => 'from_email',
            'type'       => 'text',
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'to_emails',
            'fieldName'  => 'to_emails',
            'type'       => 'simple_array',
            'nullable'   => true,
        ));
        $metadata->mapField(array(
            'columnName' => 'cc_emails',
            'fieldName'  => 'cc_emails',
            'type'       => 'simple_array',
            'nullable'   => true,
        ));
        $metadata->mapField(array(
            'columnName' => 'bcc_emails',
            'fieldName'  => 'bcc_emails',
            'type'       => 'simple_array',
            'nullable'   => true,
        ));
        $metadata->mapField(array(
            'columnName' => 'status',
            'fieldName'  => 'status',
            'type'       => 'string',
            'length'     => 80,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'fieldName'  => 'date_status',
            'columnName' => 'date_status',
            'type'       => 'datetime',
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'fieldName'  => 'date_sent',
            'columnName' => 'date_sent',
            'type'       => 'datetime',
            'nullable'   => true,
        ));
        $metadata->mapField(array(
            'fieldName'  => 'date_next_attempt',
            'columnName' => 'date_next_attempt',
            'type'       => 'datetime',
            'nullable'   => true,
        ));
        $metadata->mapField(array(
            'columnName' => 'error_code',
            'fieldName'  => 'error_code',
            'type'       => 'string',
            'length'     => 80,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'fieldName'  => 'date_created',
            'columnName' => 'date_created',
            'type'       => 'datetime',
            'nullable'   => true,
        ));
        $metadata->mapField(array(
            'columnName' => 'exec_count',
            'fieldName'  => 'exec_count',
            'type'       => 'integer',
            'nullable'   => false,
        ));

        $metadata->mapManyToOne(array(
            'fieldName'    => 'blob',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Blob',
            'dpApi'        => true,
            'dpApiDeep'    => true,
            'joinColumns'  => array(array(
                'name'                 => 'blob_id',
                'referencedColumnName' => 'id',
                'nullable'             => true,
                'onDelete'             => 'cascade',
            )),
        ));
        $metadata->mapManyToOne(array(
            'fieldName'    => 'email_account',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\EmailAccount',
            'dpApi'        => true,
            'joinColumns'  => array(array(
                'name'                 => 'email_account_id',
                'referencedColumnName' => 'id',
                'nullable'             => true,
                'onDelete'             => 'cascade',
            ))
        ));
        $metadata->mapManyToOne(array(
            'fieldName'    => 'log_blob',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Blob',
            'dpApi'        => true,
            'dpApiDeep'    => true,
            'joinColumns'  => array(array(
                'name'                 => 'log_blob_id',
                'referencedColumnName' => 'id',
                'nullable'             => true,
                'onDelete'             => 'set null',
            ))
        ));
    }

    public function __getPropValue__($k)     { return $this->$k; }
    public function __setPropValue__($k, $v) { $this->$k = $v; }
    public function __hasRunLoad__()         { return true; }

    /** @var PropertyChangedListener[] */
    private $_listeners = array();
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
