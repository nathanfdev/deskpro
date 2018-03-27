<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Orb\Util\DpStrings;
use Orb\Util\Strings;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\SavedFormRepository")
 * @ORM\Table(name="saved_forms")
 */
class SavedForm implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const TYPE_NEW_FEEDBACK = 'new_feedback';
    const TYPE_REGISTER     = 'register';
    const TYPE_ADD_EMAIL    = 'add_email';
    const TYPE_COMMENT      = 'comment';
    const TYPE_NEW_TICKET   = 'new_ticket';

    const INTENTION_VERIFY_EMAIL = 'verify_email';
    const INTENTION_LOGIN        = 'login';

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id;

    /**
     * @ORM\Column(name="data_type", type="string")
     *
     * @var string
     */
    protected $data_type;

    /**
     * @ORM\Column(name="intention_type", type="string")
     *
     * @var string
     */
    protected $intention_type;

    /**
     * @ORM\Column(name="auth_code", type="string")
     *
     * @var string
     */
    protected $auth_code;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var Person
     */
    protected $person;

    /**
     * @ORM\Column(name="form_data", type="json_array")
     *
     * @var array
     */
    protected $form_data;

    /**
     * @ORM\Column(name="meta_data", type="json_array")
     *
     * @var array
     */
    protected $meta_data;

    /**
     * @ORM\Column(name="num_sent_reminders", type="smallint")
     *
     * @var int
     */
    protected $num_sent_reminders;

    /**
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @ORM\Column(name="date_last_reminded", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $date_last_reminded;

    /**
     * @ORM\Column(name="date_expires", type="datetime")
     *
     * @var \DateTime
     */
    protected $date_expires;

    public function __construct($data_type, $intention_type, Person $person = null)
    {
        $this->setModelField('data_type', $data_type);
        $this->setModelField('intention_type', $intention_type);
        $this->setModelField('person', $person);
        $this->setModelField('num_sent_reminders', 0);
        $this->setModelField('date_created', new \DateTime());
        $this->setModelField('auth_code', DpStrings::random(25, Strings::CHARS_ALPHANUM));
        $this->setDateExpires(new \DateTime('now + 14 days')); // will be deleted if not used in 2 weeks
    }

    public function getExternalCode()
    {
        if (!$this->getId()) {
            throw new \RuntimeException(
                'a SavedForm must have an ID before you can know its ExternalCode - save it to the EM first
            ');
        }

        return $this->getId().'-'.$this->getAuthCode();
    }

    public static function parseExternalCode($code)
    {
        if ($code === null) {
            return;
        }

        $pieces = explode('-', $code);

        if (count($pieces) !== 2) {
            return;
        }

        return [
            'id'        => $pieces[0],
            'auth_code' => $pieces[1],
        ];
    }

    public function getDataType()
    {
        return $this->data_type;
    }

    public function getIntentionType()
    {
        return $this->intention_type;
    }

    /**
     * A descriptor. Used to display in a quick flash message about what this saved form is.
     *
     * @return string
     */
    public function getMessage()
    {
        switch ($this->data_type) {
            case self::TYPE_COMMENT:
                return 'comment';
            case self::TYPE_NEW_FEEDBACK:
                return 'new feedback';
            case self::TYPE_NEW_TICKET:
                return 'new ticket';
            case self::TYPE_ADD_EMAIL:
                return 'add email';
            case self::TYPE_REGISTER:
                return 'register';
        }

        return 'form';
    }

    /**
     * @return $this
     */
    public function incrementSentReminders()
    {
        $new_num = $this->num_sent_reminders + 1;
        $this->setModelField('num_sent_reminders', $new_num);
        $this->setModelField('date_last_reminded', new \DateTime());

        return $this;
    }

    public function getDateLastReminded()
    {
        return $this->date_last_reminded;
    }

    public function getNumSentReminders()
    {
        return $this->num_sent_reminders;
    }

    /**
     * @return array
     */
    public function getMetaData()
    {
        return $this->meta_data;
    }

    /**
     * @param $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getMetaDataValue($key, $default = null)
    {
        return isset($this->meta_data[$key]) ? $this->meta_data[$key] : $default;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setMetaDataValue($key, $value)
    {
        $metaData       = $this->meta_data;
        $metaData[$key] = $value;
        $this->setModelField('meta_data', $metaData);

        return $this;
    }

    /**
     * @param array $meta_data
     *
     * @return $this
     */
    public function setMetaData($meta_data)
    {
        $this->setModelField('meta_data', $meta_data);

        return $this;
    }

    /**
     * @return array
     */
    public function getFormData()
    {
        return $this->form_data;
    }

    /**
     * @param array $form_data
     *
     * @return $this
     */
    public function setFormData($form_data)
    {
        $this->setModelField('form_data', $form_data);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateExpires()
    {
        return $this->date_expires;
    }

    /**
     * @param \DateTime $date_expires
     *
     * @return $this
     */
    public function setDateExpires($date_expires)
    {
        $this->setModelField('date_expires', $date_expires);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAuthCode()
    {
        return $this->auth_code;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }
}
