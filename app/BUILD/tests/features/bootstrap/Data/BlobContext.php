<?php

namespace DpBehat\Data;

use Application\DeskPRO\Entity\Blob;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use DpBehat\BaseContext;

/**
 * Class BlobContext.
 */
class BlobContext extends BaseContext
{
    /**
     * @var DataContext
     */
    private $dataContext;

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->dataContext = $scope->getEnvironment()->getContext('DpBehat\Data\DataContext');
    }

    /**
     * @Given there are no Blob records in the DB
     */
    public function noBlobs()
    {
        $this->dataContext->noRecordsExist('AppAssetBlob');
        $this->dataContext->noRecordsExist('Blob');
    }

    /**
     * @Given I create blob with auth code :authCode
     *
     * @param string $authCode
     *
     * @return Blob
     */
    public function iCreateStringBlobWithAuthCode($authCode)
    {
        return $this->findOrCreateBlob($authCode, 'createBlobRecordFromString', [
            'blob content',
            'file.txt',
            'text/plain',
        ]);
    }

    /**
     * @Given I mark blob :blobRef as not temp
     *
     * @param string $blobRef
     */
    public function iMarkBlobAsNotTemp($blobRef)
    {
        $blob          = DataContext::getReference($blobRef);
        $blob->is_temp = false;
        $this->em()->persist($blob);
        $this->em()->flush();
    }

    /**
     * @Then blob :blobRef should be temp
     *
     * @param string $blobRef
     */
    public function blobShouldBeTemp($blobRef)
    {
        $blob    = DataContext::getReference('blob_'.$blobRef);
        $message = sprintf('Blob '.$blobRef.' is not temp', $blobRef);

        $this->assert($blob instanceof Blob && $blob->isTemp(), $message);
    }

    /**
     * @Then blob :blobRef should not be temp
     *
     * @param string $blobRef
     */
    public function blobShouldNotBeTemp($blobRef)
    {
        $blob    = DataContext::getReference('blob_'.$blobRef);
        $message = sprintf('Blob '.$blobRef.' is temp', $blobRef);

        $this->assert($blob instanceof Blob && !$blob->isTemp(), $message);
    }

    /**
     * @Given I create an image blob with auth code :authCode
     *
     * @param string $authCode
     *
     * @return Blob
     */
    public function iCreateImageBlobWithAuthCode($authCode)
    {
        return $this->findOrCreateBlob($authCode, 'createBlobRecordFromFile', [
            __DIR__.'/../../../../src/DeskPRO/Bundle/AppBundle/DataFixtures/res/avatars/superman_.jpg',
            'image.jpg',
            'image/jpeg',
        ]);
    }

    /**
     * @param string $authCode
     * @param string $createMethod
     * @param array  $params
     *
     * @return Blob
     */
    private function findOrCreateBlob($authCode, $createMethod, array $params)
    {
        $blob = $this->em()->getRepository(Blob::class)->findOneBy(['authcode' => $authCode]);
        if (!$blob) {
            /** @var Blob $blob */
            $blob = call_user_func_array([$this->get('blob.storage'), $createMethod], $params);

            $blob->setAuthCode($authCode);
            $blob->is_temp = true;

            $this->em()->persist($blob);
            $this->em()->flush();
        }

        DataContext::setReference("blob_$authCode", $blob);

        return $blob;
    }
}
