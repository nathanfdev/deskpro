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
