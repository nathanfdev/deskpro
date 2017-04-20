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

namespace DpBehat;

use Application\DeskPRO\Languages\LangPackInfo;
use Behat\Gherkin\Node\TableNode;
use DeskPRO\Bundle\AppBundle\Language\LanguageStack;

/**
 * Class LanguageContext.
 */
class LanguageContext extends BaseContext
{
    /**
     * @Given the following languages are enabled:
     */
    public function theFollowingLanguagesAreEnabled(TableNode $table)
    {
        $langs = [];
        foreach ($table->getRows() as $row) {
            $langs[] = $row[0];
        }

        // remove existing if not listed
        $existing_langs = $this->get('language_repository')->findAll();
        /** @var \Application\DeskPRO\Entity\Language $existing */
        foreach ($existing_langs as $existing) {
            if (!in_array($existing->getSystemName(), $langs)) {
                $this->em()->remove($existing);
            }
        }

        // add new if not existing
        $langpacks = new LangPackInfo();
        foreach ($langs as $lang) {
            if (!$this->getLanguageManager()->getLanguageBySystemName($lang)) {
                $new_lang = $langpacks->newLanguageEntity($lang);
                $this->em()->persist($new_lang);
            }
        }

        $this->em()->flush();
    }

    /**
     * @Then :lang_code should be the active language
     */
    public function shouldBeTheActiveLanguage($lang_code)
    {
        if (!$lang = $this->getLanguageStack()->getActive()) {
            var_dump($this->getLanguageStack());
            throw new \Exception('no active lang');
        }

        expect($lang->getSystemName())->toBe($lang_code);
    }

    /**
     * @Given :lang is the active language
     */
    public function setTheActiveLanguage($lang)
    {
        $this->getLanguageStack()->push($this->getLanguageManager()->getLanguageBySystemName($lang));
    }

    /**
     * @return LanguageStack
     */
    public function getLanguageStack()
    {
        return $this->get('language_stack');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Language\LanguageManager
     */
    public function getLanguageManager()
    {
        return $this->get('language_manager');
    }
}
