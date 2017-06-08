<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Phrase;
use Application\DeskPRO\Translate\HasPhraseName;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;

/**
 * Interface PhraseTranslatableInterface.
 */
interface PhraseTranslatableInterface extends EntityInterface, HasPhraseName
{
    /**
     * Set a collection of entity prop translations.
     *
     * @param Collection|Phrase[] $collection
     */
    public function setPhraseTranslations(Collection $collection);

    /**
     * Returns a collection of entity prop translations.
     *
     * @return Collection|Selectable|Phrase[]
     */
    public function getPhraseTranslations();

    /**
     * @param string $propName
     *
     * @return Collection|Selectable|Phrase[]
     */
    public function getPhrasePropTranslations($propName);

    /**
     * @param string   $propName
     * @param Language $language
     *
     * @return Phrase|null
     */
    public function getPhrasePropTranslation($propName, Language $language);
}
