<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DpBehat\Portal;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\FeedbackStatusCategory;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usergroup;

class GenericContext extends BasePortalContext
{
    /**
     * @Then I should not see the agent bar
     */
    public function iShouldNotSeeTheAgentBar()
    {
        expect($this->getAgentBarPage()->isAgentBarOnPage())->toBe(false);
    }

    /**
     * @Then I should see the agent bar
     */
    public function iShouldSeeTheAgentBar()
    {
        expect($this->getAgentBarPage()->isAgentBarOnPage())->toBe(true);
    }

    /**
     * @Then the agent bar should not have the admin dropdown
     */
    public function theAgentBarShouldNotHaveTheAdminDropdown()
    {
        expect($this->getAgentBarPage()->isAdminDropdownOnAgentBar())->toBe(false);
    }

    /**
     * @Then the agent bar should have the admin dropdown
     */
    public function theAgentBarShouldHaveTheAdminDropdown()
    {
        expect($this->getAgentBarPage()->isAdminDropdownOnAgentBar())->toBe(true);
    }

    /**
     * @Given the :arg1 category :arg2 exists with content titled :arg3
     */
    public function theCategoryExistsWithADownloadTitled($type, $cat_name, $content_name)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em  = $this->get('doctrine.orm.entity_manager');
        $cat = null;

        $everyone = $em->getRepository(Usergroup::class)->findOneBy([
            'sys_name' => 'everyone',
        ]);

        $person = $em->getRepository(Person::class)->findOneBy([
            'id' => 1,
        ]);

        switch ($type) {
            case 'download':
                $cat = new DownloadCategory();
                $cat->setTitle($cat_name);

                $content = new Download();
                $content->setTitle($content_name);

                $content->setCategory($cat);
                $content->setStatus('published');
                $cat->addUsergroup($everyone);

                $blob = new Blob();
                $blob->setFilename('filename.txt');
                $blob->file_url  = 'http://google.com';
                $blob->blob_hash = 'zyxasdfasdf';
                $content->setBlob($blob);
                $em->persist($blob);
                break;
            case 'feedback':
                $fcat = $em->getRepository(FeedbackCategory::class)->findOneBy([
                    'id' => 1,
                ]);

                $fstatus_cat = $em->getRepository(FeedbackStatusCategory::class)->findOneBy([
                    'id' => 1,
                ]);

                $content = new Feedback();
                $content->setStatus(Feedback::STATUS_ACTIVE);
                $content->setCategory($fcat);
                $content->setStatusCategory($fstatus_cat);
                $content->title = 'Example Feedback';

                break;
            default:
                throw new \Exception();
        }

        $content->setPerson($person);

        if ($cat) {
            $em->persist($cat);
        }
        $em->persist($content);
        $em->flush();

        // refresh to update assocation refs for the tests
        if ($cat) {
            $em->refresh($cat);
        }
        $em->refresh($content);
    }

    /**
     * @return \DpBehat\Portal\Page\AgentBar
     */
    protected function getAgentBarPage()
    {
        return $this->getPage('AgentBar');
    }
}
