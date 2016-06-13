<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Banning\EmailBanEdit;
use Application\DeskPRO\Banning\EmailBans;
use Application\DeskPRO\Banning\Form\Type\EmailBanType;
use Application\DeskPRO\Banning\Form\Type\IpBanType;
use Application\DeskPRO\Banning\IpBanEdit;
use Application\DeskPRO\Banning\IpBans;
use Application\DeskPRO\Entity\BanEmail;
use Application\DeskPRO\Entity\BanIp;
use Application\DeskPRO\EntityRepository\BanEmail as BanEmailRepository;
use Application\DeskPRO\EntityRepository\BanIp as BanIpRepository;
use Application\DeskPRO\Exception\ValidationException;
use Application\LegacyApiBundle\HttpFoundation\JsonResponse;
use Application\LegacyApiBundle\PermissionStrategy\UserTypePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class BanningController.
 *
 * @ApiModes("all")
 */
class BanningController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new UserTypePermission(UserTypePermission::AGENT);
    }

    ####################################################################################################################
    # list
    ####################################################################################################################

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $ipBanPage            = $this->in->getUInt('ip_ban_page');
        $emailBanPage         = $this->in->getUInt('email_ban_page');
        $ipBanSearchPhrase    = $this->in->getString('ip_ban_search_phrase');
        $emailBanSearchPhrase = $this->in->getString('email_ban_search_phrase');
        $emailBanWildcard     = $this->in->getUInt('email_ban_wildcard');

        $ipBans = $this->getIpBans();
        $ipBans
            ->setPage($ipBanPage)
            ->setSearchPhrase($ipBanSearchPhrase);

        $emailBans = $this->getEmailBans();
        $emailBans
            ->setPage($emailBanPage)
            ->setSearchPhrase($emailBanSearchPhrase)
            ->setWildcard($emailBanWildcard);

        return $this->createApiResponse(
            array(
                 'bans' => array(
                     'pagination' => array(
                         'ip_bans' => array(
                             'num_pages' => $ipBans->getPageCount(),
                             'page'      => $ipBanPage,
                             'total'     => $ipBans->getCount(),
                         ),
                         'email_bans' => array(
                             'num_pages' => $emailBans->getPageCount(),
                             'page'      => $emailBanPage,
                             'total'     => $emailBans->getCount(),
                         ),
                     ),
                     'ip_bans'    => $ipBans->getAllAsNestedArray(),
                     'email_bans' => $emailBans->getAllAsNestedArray(),
                 ),
            )
        );
    }

    ###################################################################################################################
    # get IP
    ####################################################################################################################

    /**
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIpAction($id)
    {
        $ipBans = $this->getIpBans();
        $ipBan  = $ipBans->getById($id);

        if (!$ipBan) {
            throw $this->createNotFoundException();
        }

        return $this->createApiResponse(
            array(
                 'ip_ban' => $this->getApiData($ipBan),
            )
        );
    }

    ###################################################################################################################
    # get Email
    ####################################################################################################################

    /**
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getEmailAction($id)
    {
        $emailBans = $this->getEmailBans();
        $emailBan  = $emailBans->getById($id);

        if (!$emailBan) {
            throw $this->createNotFoundException();
        }

        return $this->createApiResponse(
            array(
                 'email_ban' => $this->getApiData($emailBan),
            )
        );
    }

    ####################################################################################################################
    # save IP
    ####################################################################################################################

    /**
     * @param $id
     *
     * @throws ValidationException
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveIpAction($id)
    {
        $ipBans = $this->getIpBans();

        if ($id) {
            $ipBan = $ipBans->getById($id);

            if (!$ipBan) {
                throw $this->createNotFoundException();
            }
        } else {
            $ipBan = $ipBans->createNew();
        }

        $postData = $this->in->getAll('post');

        $ipBanEdit = new IpBanEdit($ipBan);

        $form = $this->createForm(new IpBanType(), $ipBanEdit, array('cascade_validation' => true));
        $form->submit($this->deleteExtraDataFromRequest($form, $postData, 'ip_ban'), true);

        if ($form->isValid()) {
            $ipBanEdit->save($this->em);
        } else {
            throw ValidationException::create($this->getFormValidationErrorsString($form));
        }

        return $this->createApiResponse(
            array(
                 'success'   => true,
                 'banned_ip' => $ipBan->banned_ip,
            )
        );
    }

    ####################################################################################################################
    # save Email
    ####################################################################################################################

    /**
     * @param $id
     *
     * @throws ValidationException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveEmailAction($id)
    {
        /** @var \Application\DeskPRO\Banning\EmailBans $emailBans */
        $emailBans = $this->getEmailBans();

        if ($id) {
            $emailBan = $emailBans->getById($id);

            if (!$emailBan) {
                throw $this->createNotFoundException();
            }
        } else {
            $emailBan = $emailBans->createNew();
        }

        $postData = $this->in->getAll('post');
        $this->createOrUpdateEmailBan($emailBan, $postData);

        return $this->createApiResponse(
            array(
                 'success'      => true,
                 'banned_email' => $emailBan->banned_email,
            )
        );
    }

    ####################################################################################################################
    # remove IP
    ####################################################################################################################

    /**
     * @param $id
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeIpAction($id)
    {
        if (null === $id) {
            /** @var BanIpRepository $banIpRepository */
            $banIpRepository = $this->em->getRepository(BanIp::class);
            $banIpRepository->removeAll();

            return $this->createSuccessResponse();
        }

        $ipBans = $this->getIpBans();
        $ipBan  = $ipBans->getById($id);

        if (!$ipBan) {
            throw $this->createNotFoundException();
        }

        $oldId = $ipBan->banned_ip;

        $this->db->beginTransaction();

        try {
            $this->em->remove($ipBan);
            $this->em->flush();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createSuccessResponse(array('old_id' => $oldId));
    }

    ####################################################################################################################
    # remove Email
    ####################################################################################################################

    /**
     * @param $id
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeEmailAction($id)
    {
        if (null === $id) {
            /** @var BanEmailRepository $banEmailRepository */
            $banEmailRepository = $this->em->getRepository(BanEmail::class);
            $banEmailRepository->removeAll();

            return $this->createSuccessResponse();
        }

        /** @var \Application\DeskPRO\Banning\EmailBans $emailBans */
        $emailBans = $this->getEmailBans();
        $emailBan  = $emailBans->getById($id);

        if (!$emailBan) {
            throw $this->createNotFoundException();
        }

        $oldId = $emailBan->banned_email;

        $this->db->beginTransaction();

        try {
            $this->em->remove($emailBan);
            $this->em->flush();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createSuccessResponse(array('old_id' => $oldId));
    }

    /**
     * export all emails into file.
     *
     * @return StreamedResponse
     */
    public function exportEmailsAction()
    {
        /** @var BanEmailRepository $banEmailRepository */
        $banEmailRepository = $this->em->getRepository(BanEmail::class);
        $response           = new StreamedResponse();
        $disp               = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'banned_emails.txt'
        );
        $response->headers->set('Content-Disposition', $disp);
        $response->setCallback(
            function () use ($banEmailRepository) {
                foreach ($banEmailRepository->getAll() as $k => $email) {
                    if ($k > 0) {
                        echo ',';
                    }
                    echo $email['banned_email'];
                }
            }
        );

        return $response;
    }

    /**
     * @param BanEmail $model
     * @param array    $data
     *
     * @throws ValidationException
     */
    protected function createOrUpdateEmailBan(BanEmail $model, array $data)
    {
        $emailBanEdit = new EmailBanEdit($model);

        $form = $this->createForm(new EmailBanType(), $emailBanEdit, array('cascade_validation' => true));
        $form->submit($this->deleteExtraDataFromRequest($form, $data, 'email_ban'), true);

        if ($form->isValid()) {
            $emailBanEdit->save($this->em);
        } else {
            throw ValidationException::create($this->getFormValidationErrorsString($form));
        }
    }

    /**
     * import emails from file.
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function importEmailsAction()
    {
        /** @var $file UploadedFile */
        if (!($file = $this->request->files->get('file')) instanceof UploadedFile) {
            throw $this->createNotFoundException();
        }

        $emailBans = $this->getEmailBans();
        $content   = file_get_contents($file->getPath().'/'.$file->getFilename());

        foreach (explode(',', $content) as $email) {
            try {
                $this->createOrUpdateEmailBan(
                    $emailBans->createNew(),
                    array('email_ban' => array('banned_email' => trim($email)))
                );
            } catch (\Exception $e) {
                // silent
            }
        }

        return $this->createApiResponse(array('filename' => true));
    }

    /**
     * @return EmailBans
     */
    private function getEmailBans()
    {
        return $this->container->getSystemService('email_bans');
    }

    /**
     * @return IpBans
     */
    private function getIpBans()
    {
        return $this->container->getSystemService('ip_bans');
    }
}
