<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage
 */

namespace DpBehat;

use Application\DeskPRO\Brand\BrandStack;
use Application\DeskPRO\EntityRepository\Language as LanguageRepo;
use Application\DeskPRO\Languages\LangPackInfo;
use Application\LanguageBundle\Language\LanguageManager;
use Application\LanguageBundle\Language\LanguageStack;
use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManager;

class LanguageContext implements Context
{
    /**
     * @var LanguageManager
     */
    private $language_manager;
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var LanguageRepo
     */
    private $lang_repo;
    /**
     * @var LanguageStack
     */
    private $lang_stack;

    public function __construct(
        LanguageManager $language_manager,
        EntityManager $em,
        LanguageRepo $lang_repo,
        LanguageStack $lang_stack
    )
    {
        $this->language_manager = $language_manager;
        $this->em = $em;
        $this->lang_repo = $lang_repo;
        $this->lang_stack = $lang_stack;
    }

    /**
     * @Given the following languages are enabled:
     */
    public function theFollowingLanguagesAreEnabled(TableNode $table)
    {
        $langs = array();
        foreach ($table->getRows() as $row) {
            $langs[] = $row[0];
        }

        // remove existing if not listed
        $existing_langs = $this->lang_repo->findAll();
        /** @var \Application\DeskPRO\Entity\Language $existing */
        foreach ($existing_langs as $existing) {
            if (!in_array($existing->getSystemName(), $langs)) {
                $this->em->remove($existing);
            }
        }

        // add new if not existing
        $langpacks = new LangPackInfo();
        foreach($langs as $lang) {
            if (!$this->lang_repo->findOneBy(array('sys_name' => $lang))) {
                $new_lang = $langpacks->newLanguageEntity($lang);
                $this->em->persist($new_lang);
            }
        }

        $this->em->flush();
    }

    /**
     * @Then :lang_code should be the active language
     */
    public function shouldBeTheActiveLanguage($lang_code)
    {
        if ($this->lang_stack->getActive()) {
            expect($this->lang_stack->getActive()->getTwoLetterLanguageCode())->toBe($lang_code);
        } else {
            var_dump($this->lang_stack->getDefaultLanguage());
            var_dump($this->lang_stack);exit;
            throw new \Exception('no active lang');
        }
    }

}
