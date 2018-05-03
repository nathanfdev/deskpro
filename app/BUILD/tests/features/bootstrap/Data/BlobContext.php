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
        $this->em()->getConnection()->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');
        $this->dataContext->noRecordsExist('AppAssetBlob');
        $this->dataContext->noRecordsExist('Blob');
        $this->em()->getConnection()->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');
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
            $this->em()->flush($blob);
        }

        DataContext::setReference("blob_$authCode", $blob);

        return $blob;
    }
}
