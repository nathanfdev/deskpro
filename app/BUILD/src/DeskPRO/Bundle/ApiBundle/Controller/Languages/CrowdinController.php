<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Languages;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Class CrowdinController.
 *
 * @ApiModes("all")
 * @ApiUserContext("admin")
 * @Rest\Route("/languages/crowdin")
 */
class CrowdinController extends BaseController
{
    const BASE_URL      = 'https://distributions.crowdin.net/c5f41ca14ee5fe46e786b27u5ra';
    const DPCS_BASE_URL = 'http://download-lang.deskpro-service.com/langauge/download-lang/c5f41ca14ee5fe46e786b27u5ra';

    /**
     * @Rest\Get("/locales")
     *
     * @return View
     */
    public function getLocalesAction()
    {
        $url      = self::BASE_URL.'/manifest.json';
        $manifest = json_decode(file_get_contents($url), true);
        $locales  = $manifest['languages'];

        return new View($this->wrap($locales));
    }

    /**
     * @Rest\Get("/{locale}/{type}/phrases", requirements={"type"="helpcenter"})
     *
     * @param string $type
     * @param string $locale
     */
    public function getPhrasesAction($locale, $type)
    {
        $url  = self::DPCS_BASE_URL."/content/develop/$locale/$type.yml";
        $path = rtrim($this->get('deskpro.app_env')->getUserFilesDir(), '/')."/crowdin.$locale.$type.yml";

        $resource = fopen($path, 'w');

        try {
            $client = new Client();
            $client->get($url, ['save_to' => $resource]);
        } catch (ClientException $e) {
            $this
                ->get('logger')
                ->warn("Failed to download language distribution via DPCS, defaulting to CrowdIn: {$e->getMessage()}")
            ;

            try {
                $url = self::BASE_URL."/content/develop/$locale/$type.yml";
                $client->get($url, ['save_to' => $resource]);
            } catch (\Exception $ee) {
                error_log($ee->getMessage());
            }
        }

        $data    = explode("\n", file_get_contents($path));
        $phrases = [];
        foreach ($data as $item) {
            if (strpos($item, 'helpcenter.') !== false && strpos($item, ':') !== false) {
                list($phrase, $content) = explode(':', $item);

                $phrases[$phrase] = trim($content, '\' ');
            }
        }

        unlink($path);

        return new View($this->wrap($phrases));
    }
}
