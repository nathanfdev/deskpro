<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Person;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Entity\PersonEmail;
use DeskPRO\Bundle\AppBundle\Serializer\Model\ProfileAvatar;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * User profile settings.
 *
 * Class PersonProfile
 */
class PersonProfile
{
    /**
     * The unique person ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * Person name.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * Person overridden name.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $display_name;

    /**
     * Person primary email.
     *
     * @JMS\Type("to_string<Application\DeskPRO\Entity\PersonEmail>")
     *
     * @var PersonEmail
     */
    private $primary_email;

    /**
     * Person emails list.
     *
     * @JMS\Type("collection<to_string<Application\DeskPRO\Entity\PersonEmail>>")
     *
     * @var ArrayCollection
     */
    private $emails;

    /**
     * Person phone.
     *
     * @JMS\Type("array<string>")
     *
     * @var array|null
     */
    private $phone;

    /**
     * Person language.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Language>")
     *
     * @var Language
     */
    private $language_id;

    /**
     * Person timezone.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $timezone;

    /**
     * Persons avatar.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\ProfileAvatar")
     *
     * @var \DeskPRO\Bundle\AppBundle\Serializer\Model\ProfileAvatar|null
     */
    private $avatar;

    /**
     * Constructor.
     *
     * @param PersonEntity       $person
     * @param ProfileAvatar|null $avatar
     */
    public function __construct(PersonEntity $person, $avatar)
    {
        $this->id            = $person->getId();
        $this->name          = $person->getName();
        $this->display_name  = $person->getOverrideDisplayName();
        $this->primary_email = $person->getPrimaryEmail();
        $this->emails        = $person->getEmails();
        $this->language_id   = $person->getLanguage();
        $this->timezone      = $person->getTimezone();

        $phone_number     = $person->getPrimaryPhoneNumber();
        $phone_serialized = null;
        if ($phone_number) {
            $phone_serialized = [
                'number'    => $phone_number->getNumberFormatted(),
                'extension' => $phone_number->getExt(),
            ];
        }
        $this->phone = $phone_serialized;

        $this->avatar = $avatar;
    }
}
