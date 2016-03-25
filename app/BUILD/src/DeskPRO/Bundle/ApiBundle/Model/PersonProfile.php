<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\Model;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use DeskPRO\Bundle\AppBundle\Content\Avatar;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * User profile settings.
 *
 * Class PersonProfile
 *
 * @JMS\ExclusionPolicy("none")
 */
class PersonProfile
{
    /**
     * @JMS\Exclude()
     *
     * @var Person
     */
    private $person;

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
     * Person overriden name.
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
     * @JMS\Type("array<array<string>>")
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
     * @JMS\Type("DeskPRO\Bundle\ApiBundle\Model\ProfileAvatar")
     *
     * @var ProfileAvatar|null
     */
    private $avatar;

    /**
     * Constructor.
     *
     * @param Person $person
     */
    public function __construct(Person $person)
    {
        $this->person        = $person;
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
    }

    public function setAvatar(Avatar $avatar)
    {
        if ($this->person->getPictureBlob()) {
            $this->avatar = new ProfileAvatar(
                $this->person->getPictureBlob()->getAuthId(),
                $avatar->getUrl(200)
            );
        }
    }
}
