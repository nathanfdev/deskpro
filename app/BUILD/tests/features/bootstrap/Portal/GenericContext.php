<?php

namespace DpBehat\Portal;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\EmailGateway\Reader\EzcReader;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CategoryAbstract;
use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\FeedbackStatusCategory;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usergroup;
use Application\EmailBundle\Entity\SendmailSource;
use Application\EmailBundle\EntityRepository\SendmailSourceRepository;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use DpBehat\Api\BrandContext;
use DpBehat\Data\DataContext;

class GenericContext extends BasePortalContext
{
    /**
     * @var BrandContext
     */
    private $brandContext;

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment        = $scope->getEnvironment();
        $this->brandContext = $environment->getContext(BrandContext::class);
    }

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
     * @Then I should see a :type flash message
     */
    public function iShouldSeeAFlashMessage($type)
    {
        $this->assertSession()->elementExists('css', sprintf('.flash.flash-%s', $type));
    }

    /**
     * @Then :user should be subscribed to the :content_type content :title
     */
    public function userShouldBeSubscribedToTheCategory($user, $content_type, $title)
    {
        $person  = $this->get('user_details')->getWho($user);
        $content = $this->getContent($content_type, $title);

        expect($this->isSubscribedContent($person, $content))->toBe(true);
    }

    /**
     * @Then :user should be subscribed to the :content_type category :title
     */
    public function userShouldBeSubscribedToTheContent($user, $content_type, $title)
    {
        $person = $this->get('user_details')->getWho($user);
        $cat    = $this->getCategory($content_type, $title);

        expect($this->isSubscribedCat($person, $cat))->toBe(true);
    }

    /**
     * @Then I should receive an email with the subject phrase :subject_phrase
     */
    public function iShouldReceiveAnEmailWithTheSubjectPhrase($subject_phrase)
    {
        $subject = $this->phrase($subject_phrase);

        expect($this->getSubjectOfLastEmail())->toBe($subject);
    }

    /**
     * @Then I should receive an email on :who with the subject phrase :subject
     */
    public function iShouldReceiveAnEmailOnMyEmailAddressWithTheSubjectPhrase($who, $subject_phrase)
    {
        if (strpos($who, '@') === false) {
            $email = $this->get('user_details')->getEmail($who);
        } else {
            $email = $who;
        }

        $subject = $this->phrase($subject_phrase);

        expect($this->getSubjectOfLastEmail($email))->toBe($subject);
    }

    /**
     * @Then I should receive an email with the subject :subject
     */
    public function iShouldReceiveAnEmailWithTheSubject($subject)
    {
        expect($this->getSubjectOfLastEmail())->toBe($subject);
    }

    /**
     * @Then I should receive an email on :who with the subject :subject
     */
    public function iShouldReceiveAnEmailOnMyEmailAddressWithTheSubject($who, $subject)
    {
        if (strpos($who, '@') === false) {
            $email = $this->get('user_details')->getEmail($who);
        } else {
            $email = $who;
        }

        expect($this->getSubjectOfLastEmail($email))->toBe($subject);
    }

    /**
     * @Then I should see a :type flash message with the phrase :phrase
     */
    public function iShouldSeeAFlashMessageWithThePhrase($type, $phrase)
    {
        $selector = sprintf('.flash.flash-%s', $type);
        $this->assertSession()->elementExists('css', $selector);
        $this->assertSession()->elementTextContains('css', $selector, $this->phrase($phrase));
    }

    /**
     * @Then I should be on the set password page
     */
    public function iShouldBeOnTheSetPasswordPage()
    {
        $this->assertSession()->addressMatches('#/login/set-password/.*#');
        $this->assertSession()->pageTextContains('Set Password');
    }

    /**
     * @Given the setting :setting_name is set to :val
     */
    public function theSettingIsSetTo($setting_name, $val)
    {
        $this->em()->getConnection()->executeUpdate(
            'REPLACE INTO settings (name, value) VALUES (:name, :value)',
            ['name' => $setting_name, 'value' => $val]
        );
    }

    /**
     * @Given the :brand setting :setting_name is set to :val
     *
     * @param string $brand
     * @param string $setting_name
     * @param string $val
     *
     * @throws \Exception
     */
    public function theBrandSettingIsSetTo($brand, $setting_name, $val)
    {
        $brand = DataContext::resolveReference($brand);
        if (!$brand instanceof Brand) {
            throw new \Exception('Brand not found');
        }

        $this->em()->getConnection()->executeUpdate(
            'REPLACE INTO settings_brand (name, value, brand_id) VALUES (:name, :value, :brand_id)',
            ['name' => $setting_name, 'value' => $val, 'brand_id' => $brand->getId()]
        );

        $this->container()->get('settings_resolver')->getBrandSettings($brand, true);
        $this->container()->get('brand_stack')->push($brand, true);
    }

    /**
     * @When I click the email verification link
     */
    public function iClickTheEmailVerificationLink()
    {
        $link = $this->getFirstLinkInLastEmail();
        $url  = parse_url($link);

        $this->getSession()->visit($url['path']);
    }

    /**
     * @When I click the email verification link received on :who
     *
     * @param $who
     */
    public function iClickTheEmailVerificationLinkReceivedOn($who)
    {
        if (strpos($who, '@') === false) {
            $email = $this->get('user_details')->getEmail($who);
        } else {
            $email = $who;
        }

        $link = $this->getFirstLinkInLastEmail($email);
        $url  = parse_url($link);

        $this->getSession()->visit($url['path']);
    }

    /**
     * @Given the :type root category ":name" exists
     */
    public function categoryExists($type, $title)
    {
        $em      = $this->get('doctrine.orm.entity_manager');
        $classes = [
            'KB' => ArticleCategory::class,
        ];
        if (!array_key_exists($type, $classes)) {
            throw new \Exception("Unknown category type $type");
        }
        $class = $classes[$type];

        if (!$em->getRepository($class)->findOneBy(compact('title'))) {
            $category = new $class();
            $category->setTitle($title);
            $category->root = 1;
            $em->persist($category);
            $em->flush();
        }
    }

    /**
     * @Given the :type category :cat_name exists with content titled :content_name
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
                $cat         = $em->getRepository(DownloadCategory::class)->findOneBy(['title' => $cat_name]);
                $cat or $cat = new DownloadCategory();
                $cat->setTitle($cat_name);
                $cat->setBrand($this->brandContext->iHaveDefaultBrand());

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

    /**
     * @return SendmailSource
     */
    protected function getLastEmail($email = null)
    {
        /** @var SendmailSourceRepository $ss_repo */
        $ss_repo = $this->repository(SendmailSource::class);

        return $ss_repo->getLatest($email);
    }

    protected function isSubscribedContent(Person $person, ContentAbstract $content)
    {
        return $this->get('subscriptions_helper')->isSubscribedContent($content, $person);
    }

    protected function getFirstLinkInLastEmail($email = null)
    {
        $email_data = $this->getLastEmailData($email);

        $regex = '/https?\:\/\/[^\" \s]+/i';
        if (preg_match($regex, $email_data['body'], $matches)) {
            return $matches[0];
        }

        return;
    }

    protected function getBodyOfLastEmail()
    {
        $email_data = $this->getLastEmailData();

        return $email_data['body'];
    }

    protected function getSubjectOfLastEmail($email = null)
    {
        $email_data = $this->getLastEmailData($email);

        return $email_data['subject'];
    }

    protected function isSubscribedCat(Person $person, CategoryAbstract $content)
    {
        return $this->get('subscriptions_helper')->isSubscribedCategory($content, $person);
    }

    protected function getCategory($type, $title)
    {
        return $this->repository($this->getContentCatClass($type))->findOneBy([
            'title' => $title,
        ]);
    }

    /**
     * An array of data that comes from the last email saved to "sendmail_sources".
     *
     * @return array
     */
    protected function getLastEmailData($email = null)
    {
        $last_email = $this->getLastEmail($email);

        $blob = $last_email->getBlob();

        /** @var DeskproBlobStorage $blob_storage */
        $blob_storage = $this->get('deskpro.blob_storage');

        $email_body    = $blob_storage->copyBlobRecordToString($blob);
        $email_subject = $last_email->getHeaderSubject();

        /** @var EzcReader $reader */
        $reader = $this->get('email.ezc_reader_factory')->create();
        $reader->setRawSource($email_body);
        $email_body = $reader->getBodyText()->getBodyUtf8();

        return [
            'body'    => $email_body,
            'subject' => $email_subject,
        ];
    }

    protected function getContent($type, $title)
    {
        return $this->repository($this->getContentClass($type))->findOneBy([
            'title' => $title,
        ]);
    }

    protected function getContentCatClass($type)
    {
        switch ($type) {
            case 'kb':
                return ArticleCategory::class;
            case 'download':
                return DownloadCategory::class;
            case 'news':
                return NewsCategory::class;
            case 'feedback':
                return FeedbackCategory::class;
        }
    }

    protected function getContentClass($type)
    {
        switch ($type) {
            case 'kb':
                return Article::class;
            case 'download':
                return Download::class;
            case 'news':
                return News::class;
            case 'feedback':
                return Feedback::class;
        }
    }
}
