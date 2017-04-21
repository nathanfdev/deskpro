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

namespace DeskPRO\Bundle\ApiBundle\Controller\EmailAccounts;

use Application\DeskPRO\Entity\EmailAccount;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * API to access email accounts.
 *
 * @ApiModes("all")
 * @ApiUserContext("admin")
 * @ApiDoc(target="all", section="Email accounts", output="Application\DeskPRO\Entity\EmailAccount")
 * @Rest\Route("/email_accounts")
 */
class EmailAccountsController extends CrudController
{
    public static $exposeOnly = ['list'];
    public static $entity     = EmailAccount::class;

    /**
     * Get resource with provided id.
     *
     * @ApiDoc(
     *     description="Upload a certificate and a key",
     *     requirements={
     *         {
     *             "name"="id",
     *             "requirement"="default|\d+",
     *             "description"="The id of the email account",
     *             "dataType"="integer"
     *         }
     *     },
     *     statusCodes={
     *         200="We will return such status in case we found your entity",
     *         404="Not Found error will returned in case we can't find entity with specified ID"
     *     },
     *     noInput=true,
     *     output="Application\DeskPRO\Entity\EmailAccount"
     * )
     * @Rest\Post("/{id}/encryption", requirements={"id"="default|\d+"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View|NotFoundHttpException
     */
    public function uploadCertificateAction(Request $request, $id)
    {
        $accept = $this->getContainer()->getAttachmentAccepter();
        $em     = $this->getManager();

        $certificate = $request->files->get('cert');
        $key         = $request->files->get('key');
        $passPhrase  = $request->get('pass_phrase');
        $certString  = file_get_contents($certificate->getPathname());

        $info = openssl_x509_parse($certString);
        if (!$info) {
            return $this->createNotFoundException('Error: Not a certificate');
        }

        if (!isset($info['subject']['emailAddress'])) {
            return $this->createNotFoundException('Error: Not an email certificate');
        }

        /** @var EmailAccount $account */
        $account = $this->getRepository(EmailAccount::class)->find($id);
        if (!$account) {
            return $this->createNotFoundException('Error: can not find account');
        }

        if ($account->getEmailAddressMatch($info['subject']['emailAddress']) === null) {
            return $this->createNotFoundException(
                'Error: certificate email address: '.$info['subject']['emailAddress'].' does not match account'
            );
        }

        $certBlob = $accept->accept($certificate);
        $keyBlob  = $accept->accept($key);

        $account
            ->setCertBlob($certBlob)
            ->setKeyBlob($keyBlob)
            ->setKeyPassPhrase($passPhrase)
        ;

        $em->persist($account);
        $em->flush();

        return View::create($this->wrap($account));
    }

    /**
     * Get resource with provided id.
     *
     * @ApiDoc(
     *      description="Delete a certificate",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="default|\d+",
     *              "description"="The id of the email account",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="We will return such status in case we found your entity",
     *          404="Not Found error will returned in case we can't find entity with specified ID"
     *      }
     * )
     * @Rest\Delete("/{id}/certificate", requirements={"id"="default|\d+"})
     *
     * @param int $id
     *
     * @return View
     */
    public function deleteCertificateAction($id)
    {
        /** @var EmailAccount $account */
        $account = $this->getRepository(EmailAccount::class)->find($id);
        if (!$account) {
            return $this->createNotFoundException('Error: can not find account');
        }

        $blob = $account->getCertBlob();
        $em   = $this->getManager();
        $em->remove($blob);
        $account->setCertBlob(null);
        $em->flush();

        return View::create([], Response::HTTP_OK);
    }

    /**
     * Get resource with provided id.
     *
     * @ApiDoc(
     *      description="Delete a key",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="default|\d+",
     *              "description"="The id of the email account",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="We will return such status in case we found your entity",
     *          404="Not Found error will returned in case we can't find entity with specified ID"
     *      }
     * )
     * @Rest\Delete("/{id}/key", requirements={"id"="default|\d+"})
     *
     * @param int $id
     *
     * @return View
     */
    public function deleteKeyAction($id)
    {
        /** @var EmailAccount $account */
        $account = $this->getRepository(EmailAccount::class)->find($id);
        if (!$account) {
            return $this->createNotFoundException('Error: can not find account');
        }

        $blob = $account->getKeyBlob();
        $em   = $this->getManager();
        $em->remove($blob);
        $account->setKeyBlob(null);
        $em->flush();

        return View::create([], Response::HTTP_OK);
    }
}
