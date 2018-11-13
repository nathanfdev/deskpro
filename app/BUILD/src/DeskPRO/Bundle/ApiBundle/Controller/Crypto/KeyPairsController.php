<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Crypto;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides RSA keypair operations
 *
 * @Rest\Route("/crypto/keypairs")
 * @ApiDoc(target="all", section="Cryptography")
 * @ApiModes("all")
 * @ApiUserContext("admin")
 */
class KeyPairsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Creates a new RSA keypair",
     *      statusCodes={
     *          200="Returned in case of successful resource creation",
     *          500="Returned when keypair generation fails",
     *      }
     * )
     * @Rest\Post("")
     * @Rest\Get("")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function postAction(Request $request)
    {
        $config = [
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        // Create the private and public key
        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privateKey);
        $keyPairDetails = openssl_pkey_get_details($res);
        $publicKey = $keyPairDetails["key"];
        openssl_pkey_free($res);

        return new JsonResponse([
            'publicKey' => $publicKey,
            'privateKey' => $privateKey,
        ]);
    }
}
