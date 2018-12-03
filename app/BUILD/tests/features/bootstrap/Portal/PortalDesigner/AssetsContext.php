<?php

namespace DpBehat\Portal\PortalDesigner;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use DeskPRO\Bundle\PortalBundle\Designer\AssetsManager;
use Doctrine\ORM\EntityManager;
use DpBehat\Http\HttpContext;
use DpBehat\Portal\BasePortalContext;

/**
 * Class AssetsContext.
 */
class AssetsContext extends BasePortalContext
{
    /**
     * @var HttpContext
     */
    private $http_context;

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment        = $scope->getEnvironment();
        $this->http_context = $environment->getContext(HttpContext::class);
    }

    /**
     * @Given there are no custom portal assets
     */
    public function thereAreNoCustomPortalAssets()
    {
        /** @var EntityManager $em */
        $em     = $this->get('doctrine.orm.entity_manager');
        $assets = $em->getRepository(ThemeSetAsset::class)->findBy(['tags' => [AssetsManager::CUSTOM_ASSET_TAG]]);
        foreach ($assets as $asset) {
            $em->remove($asset);
        }
        $em->flush();
    }

    /**
     * @When I send a DELETE request to the just uploaded custom portal asset
     */
    public function iSendADeleteRequestToTheJustUploadedCustomPortalAsset()
    {
        $file_id = $this->http_context->getLastFileUploadResponse()['data']['id'];
        $url     = '/portal/api/style/edit-theme-set/assets/'.$file_id;

        /** @var \Symfony\Bundle\FrameworkBundle\Client $client */
        $client = $this->getMink()->getSession()->getDriver()->getClient();

        $client->request('DELETE', $url);
    }

    /**
     * @Then created blob content should contain :expectedText
     *
     * @param string $expectedText
     *
     * @throws \Exception
     */
    public function createdBlobContentShouldContain($expectedText)
    {
        $id = $this->http_context->getLastFileUploadResponse()['data']['id'];

        /** @var ThemeSetAsset $asset */
        $asset = $this->em()->getRepository(ThemeSetAsset::class)->find($id);
        if (!$asset) {
            throw new \Exception("Asset $id not found");
        }

        $content = $this->container()->get('blob.storage')->copyBlobRecordToString($asset->getBlob());

        if (strpos($content, $expectedText) === -1) {
            throw new \Exception("Uploaded file doesn't contain $expectedText");
        }
    }

    /**
     * @Then the portal theme set css should contain :expectedText
     *
     * @param string $expectedText
     *
     * @throws \Exception
     */
    public function portalThemeSetCssContains($expectedText)
    {
        $styleManager = $this->container()->get('dp.portal.designer.styles_manager');

        if (!$blob = $styleManager->getCssBlob()) {
            throw new \Exception('Portal theme css not found');
        }

        $css = $this->container()->get('blob.storage')->copyBlobRecordToString($blob);
        if (strpos($css, $expectedText) === -1) {
            throw new \Exception("Portal theme css doesn't contain $expectedText");
        }
    }
}
