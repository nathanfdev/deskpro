<?php

namespace DpBehat\Api;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use DpBehat\BaseContext;
use DpBehat\Data\DataContext;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class SplashImageContext.
 */
class SplashImageContext extends BaseContext
{
    public const DUMMY_FILE_ONE_PNG = 'image.png';
    public const DUMMY_FILE_TWO_PNG = 'image2.png';
    private $image;

    /**
     * @var DataContext
     * */
    private DataContext $dataContext;

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment       = $scope->getEnvironment();
        $this->dataContext = $environment->getContext(DataContext::class);
    }

    /**
     * @Then /^I attach image file to my request$/
     */
    public function iAttachImageFileToMyRequest()
    {
        $this->image['file'] = new UploadedFile(
            $this->getTestDir('features/bootstrap/files/'.self::DUMMY_FILE_ONE_PNG),
            self::DUMMY_FILE_ONE_PNG,
            'image/png'
        );
        DataContext::setPlaceholder('DummyImage', $this->image);
    }

}
