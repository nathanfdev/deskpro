<?php

/**
 * DeskPRO.
 */
namespace Application\LegacyApiBundle\Controller;

use Application\LegacyApiBundle\PermissionStrategy\UserTypePermission;
use Application\DeskPRO\Banning\EmailBanEdit;
use Application\DeskPRO\Banning\EmailBans;
use Application\DeskPRO\Banning\Form\Type\EmailBanType;
use Application\DeskPRO\Banning\Form\Type\IpBanType;
use Application\DeskPRO\Banning\IpBanEdit;
use Application\DeskPRO\EntityRepository\BanEmail;
use Application\DeskPRO\Exception\ValidationException;
use DeskPRO\Component\Filesystem\SafeFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function listAction()
    {
        $ip_ban_page             = $this->in->getUint('ip_ban_page');
        $email_ban_page          = $this->in->getUint('email_ban_page');
        $ip_ban_search_phrase    = $this->in->getString('ip_ban_search_phrase');
        $email_ban_search_phrase = $this->in->getString('email_ban_search_phrase');
        $email_ban_wildcard      = $this->in->getUInt('email_ban_wildcard');

        /*
         * @var \Application\DeskPRO\Banning\IpBans
         */
        $ip_bans = $this->container->getSystemService('ip_bans');
        $ip_bans
            ->setPage($ip_ban_page)
            ->setSearchPhrase($ip_ban_search_phrase);

        /*
         * @var \Application\DeskPRO\Banning\EmailBans
         */
        $email_bans = $this->container->getSystemService('email_bans');
        $email_bans
            ->setPage($email_ban_page)
            ->setSearchPhrase($email_ban_search_phrase)
            ->setWildcard($email_ban_wildcard);

        return $this->createApiResponse(
            array(
                 'bans' => array(
                     'pagination' => array(
                         'ip_bans' => array(
                             'num_pages' => $ip_bans->getPageCount(),
                             'page'      => $ip_ban_page,
                             'total'     => $ip_bans->getCount(),
                         ),
                         'email_bans' => array(
                             'num_pages' => $email_bans->getPageCount(),
                             'page'      => $email_ban_page,
                             'total'     => $email_bans->getCount(),
                         ),
                     ),
                     'ip_bans'    => $ip_bans->getAllAsNestedArray(),
                     'email_bans' => $email_bans->getAllAsNestedArray(),
                 ),
            )
        );
    }

    ###################################################################################################################
    # get IP
    ####################################################################################################################

    public function getIpAction($id)
    {
        /*
         * @var \Application\DeskPRO\Banning\IpBans
         */
        $ip_bans = $this->container->getSystemService('ip_bans');
        $ip_ban  = $ip_bans->getById($id);

        if (!$ip_ban) {
            throw $this->createNotFoundException();
        }

        return $this->createApiResponse(
            array(
                 'ip_ban' => $this->getApiData($ip_ban),
            )
        );
    }

    ###################################################################################################################
    # get Email
    ####################################################################################################################

    public function getEmailAction($id)
    {
        /*
         * @var \Application\DeskPRO\Banning\EmailBans
         */
        $email_bans = $this->container->getSystemService('email_bans');
        $email_ban  = $email_bans->getById($id);

        if (!$email_ban) {
            throw $this->createNotFoundException();
        }

        return $this->createApiResponse(
            array(
                 'email_ban' => $this->getApiData($email_ban),
            )
        );
    }

    ####################################################################################################################
    # save IP
    ####################################################################################################################

    public function saveIpAction($id)
    {
        /*
         * @var \Application\DeskPRO\Banning\IpBans
         */
        $ip_bans = $this->container->getSystemService('ip_bans');

        if ($id) {
            $ip_ban = $ip_bans->getById($id);

            if (!$ip_ban) {
                throw $this->createNotFoundException();
            }
        } else {
            $ip_ban = $ip_bans->createNew();
        }

        $postData = $this->in->getAll('post');

        $ip_ban_edit = new IpBanEdit($ip_ban);

        $form = $this->createForm(new IpBanType(), $ip_ban_edit, array('cascade_validation' => true));
        $form->submit($this->deleteExtraDataFromRequest($form, $postData, 'ip_ban'), true);

        if ($form->isValid()) {
            $ip_ban_edit->save($this->em);
        } else {
            throw ValidationException::create($this->getFormValidationErrorsString($form));
        }

        return $this->createApiResponse(
            array(
                 'success'   => true,
                 'banned_ip' => $ip_ban->banned_ip,
            )
        );
    }

    ####################################################################################################################
    # save Email
    ####################################################################################################################

    public function saveEmailAction($id)
    {
        /*
         * @var \Application\DeskPRO\Banning\EmailBans
         */
        $email_bans = $this->container->getSystemService('email_bans');

        if ($id) {
            $email_ban = $email_bans->getById($id);

            if (!$email_ban) {
                throw $this->createNotFoundException();
            }
        } else {
            $email_ban = $email_bans->createNew();
        }

        $postData = $this->in->getAll('post');
        $this->createOrUpdateEmailBan($email_ban, $postData);

        return $this->createApiResponse(
            array(
                 'success'      => true,
                 'banned_email' => $email_ban->banned_email,
            )
        );
    }

    ####################################################################################################################
    # remove IP
    ####################################################################################################################

    public function removeIpAction($id)
    {
        if (null === $id) {
            $this->em->getRepository('DeskPRO:BanIp')->removeAll();

            return $this->createSuccessResponse();
        }

        /*
         * @var \Application\DeskPRO\Banning\IpBans
         */
        $ip_bans = $this->container->getSystemService('ip_bans');
        $ip_ban  = $ip_bans->getById($id);

        if (!$ip_ban) {
            throw $this->createNotFoundException();
        }

        $old_id = $ip_ban->banned_ip;

        $this->db->beginTransaction();

        try {
            $this->em->remove($ip_ban);
            $this->em->flush();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createSuccessResponse(array('old_id' => $old_id));
    }

    ####################################################################################################################
    # remove Email
    ####################################################################################################################

    public function removeEmailAction($id)
    {
        /*
         * @var \Application\DeskPRO\Banning\EmailBans $email_bans
         */

        if (null === $id) {
            $this->em->getRepository('DeskPRO:BanEmail')->removeAll();

            return $this->createSuccessResponse();
        }

        $email_bans = $this->container->getSystemService('email_bans');
        $email_ban  = $email_bans->getById($id);

        if (!$email_ban) {
            throw $this->createNotFoundException();
        }

        $old_id = $email_ban->banned_email;

        $this->db->beginTransaction();

        try {
            $this->em->remove($email_ban);
            $this->em->flush();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createSuccessResponse(array('old_id' => $old_id));
    }

    /**
     * export all emails into file.
     *
     * @return StreamedResponse
     */
    public function exportEmailsAction()
    {
        /* @var BanEmail $bs */
        $rep = $this->em->getRepository('DeskPRO:BanEmail');

        $response = new StreamedResponse();
        $disp     = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'banned_emails.txt'
        );
        $response->headers->set('Content-Disposition', $disp);
        $response->setCallback(function () use ($rep) {
            foreach ($rep->getAll() as $k => $email) {
                if ($k > 0) {
                    echo ',';
                }
                echo $email['banned_email'];
            }
        });

        return $response;
    }

    /**
     * @param \Application\DeskPRO\Entity\BanEmail $model
     * @param array                                $data
     *
     * @throws \Application\DeskPRO\Exception\ValidationException
     */
    protected function createOrUpdateEmailBan(\Application\DeskPRO\Entity\BanEmail $model, array $data)
    {
        $email_ban_edit = new EmailBanEdit($model);

        $form = $this->createForm(new EmailBanType(), $email_ban_edit, array('cascade_validation' => true));
        $form->submit($this->deleteExtraDataFromRequest($form, $data, 'email_ban'), true);

        if ($form->isValid()) {
            $email_ban_edit->save($this->em);
        } else {
            throw ValidationException::create($this->getFormValidationErrorsString($form));
        }
    }

    /**
     * import emails from file.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function importEmailsAction()
    {
        /** @var $file UploadedFile */
        if (!($file = $this->request->files->get('file')) instanceof UploadedFile) {
            throw $this->createNotFoundException();
        }

        /** @var EmailBans $email_bans */
        $email_bans = $this->container->getSystemService('email_bans');
        $content    = SafeFile::fileGetContents($file->getPath().'/'.$file->getFilename(), dirname($file->getRealPath()));

        foreach (explode(',', $content) as $email) {
            try {
                $this->createOrUpdateEmailBan(
                    $email_bans->createNew(),
                    array('email_ban' => array('banned_email' => trim($email)))
                );
            } catch (\Exception $e) {
                // silent
            }
        }

        return $this->createApiResponse(array('filename' => true));
    }
}
