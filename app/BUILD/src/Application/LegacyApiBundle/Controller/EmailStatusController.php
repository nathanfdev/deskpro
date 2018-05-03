<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Email\EmailSource\Finder as EmailSourceFinder;
use Application\DeskPRO\Email\EmailSource\FinderFilter as EmailSourceFinderFilter;
use Application\DeskPRO\Email\SendmailSource\Finder as SendmailSourceFinder;
use Application\DeskPRO\Email\SendmailSource\FinderFilter as SendmailSourceFinderFilter;
use Application\DeskPRO\EmailGateway\Runner;
use Application\EmailBundle\Entity\SendmailSource;
use Application\LegacyApiBundle\PermissionStrategy\UserTypePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Doctrine\DBAL\Connection;
use Orb\Util\Strings;

/**
 * @ApiModes("all")
 */
class EmailStatusController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new UserTypePermission(UserTypePermission::ADMIN);
    }

    //###################################################################################################################
    // get-email-sources
    //###################################################################################################################

    public function listSourcesAction()
    {
        //------------------------------
        // Filter options
        //------------------------------

        $filter       = new EmailSourceFinderFilter();
        $filter_input = $this->in->getArrayValue('filter');
        $form         = $this->createFormBuilder($filter)
                     ->add('page', 'text')
                     ->add(
                         'statuses',
                         'choice',
                         [
                             'choices'  => array_combine($filter->getValidStatuses(), $filter->getValidStatuses()),
                             'required' => false,
                             'multiple' => true,
                         ]
                     )
                     ->add(
                         'date_start',
                         'date',
                         [
                             'view_timezone' => $this->person->getTimezone(),
                             'widget'        => 'single_text',
                             'input'         => 'datetime',
                             'required'      => false,
                         ]
                     )
                     ->add(
                         'date_end',
                         'date',
                         [
                             'view_timezone' => $this->person->getTimezone(),
                             'widget'        => 'single_text',
                             'input'         => 'datetime',
                             'required'      => false,
                         ]
                     )
                     ->add(
                         'subject',
                         'text',
                         [
                             'required' => false,
                         ]
                     )
                     ->add(
                         'account',
                         'text',
                         [
                             'required' => false,
                         ]
                     )
                     ->add(
                         'to',
                         'text',
                         [
                             'required' => false,
                         ]
                     )
                     ->add(
                         'from',
                         'text',
                         [
                             'required' => false,
                         ]
                     )
                     ->add(
                         'error_code',
                         'text',
                         [
                             'required' => false,
                         ]
                     )
                     ->getForm();

        $form->submit($filter_input);

        $finder = new EmailSourceFinder($this->em, $filter);

        $info    = $finder->getPageInfo();
        $results = $finder->getResults();

        return $this->createApiResponse(
            [
                'page'          => $filter->getPage(),
                'num_pages'     => $info['num_pages'],
                'count'         => $info['count'],
                'email_sources' => $this->getApiData($results),
            ]
        );
    }

    //###################################################################################################################
    // get-email-sources-stats
    //###################################################################################################################

    public function sourcesStatsAction()
    {
        $status_counts  = $this->db->fetchAllKeyValue('SELECT status, COUNT(*) FROM email_sources GROUP BY status');
        $account_counts = $this->db->fetchAllKeyValue(
            'SELECT email_account_id, COUNT(*) FROM email_sources GROUP BY email_account_id'
        );

        return $this->createJsonResponse(
            [
                'by_status'  => $status_counts,
                'by_account' => $account_counts,
            ]
        );
    }

    //###################################################################################################################
    // list-sendmail
    //###################################################################################################################

    public function listSendmailAction()
    {
        //------------------------------
        // Filter options
        //------------------------------

        $filter       = new SendmailSourceFinderFilter();
        $filter_input = $this->in->getArrayValue('filter');
        $form         = $this->createFormBuilder($filter)
                     ->add('page', 'text')
                     ->add(
                         'statuses',
                         'choice',
                         [
                             'choices'  => array_combine($filter->getValidStatuses(), $filter->getValidStatuses()),
                             'required' => false,
                             'multiple' => true,
                         ]
                     )
                     ->add(
                         'date_start',
                         'date',
                         [
                             'view_timezone' => $this->person->getTimezone(),
                             'widget'        => 'single_text',
                             'input'         => 'datetime',
                             'required'      => false,
                         ]
                     )
                     ->add(
                         'date_end',
                         'date',
                         [
                             'view_timezone' => $this->person->getTimezone(),
                             'widget'        => 'single_text',
                             'input'         => 'datetime',
                             'required'      => false,
                         ]
                     )
                     ->add(
                         'subject',
                         'text',
                         [
                             'required' => false,
                         ]
                     )
                     ->add(
                         'to',
                         'text',
                         [
                             'required' => false,
                         ]
                     )
                     ->add(
                         'from',
                         'text',
                         [
                             'required' => false,
                         ]
                     )
                     ->add(
                         'error_code',
                         'text',
                         [
                             'required' => false,
                         ]
                     )
                     ->getForm();

        $form->submit($filter_input);

        $finder = new SendmailSourceFinder($this->em, $filter);

        $info    = $finder->getPageInfo();
        $results = $finder->getResults();

        $data = [];
        foreach ($results as $r) {
            $res = $r->toArray();
            if (defined('DPC_IS_CLOUD') && !DPC_SITE_IS_APPROVED) {
                // dont reveal rate limit error code
                $res['status']     = SendmailSource::STATUS_COMPLETE;
                $res['error_code'] = null;
            }
            $data[] = $res;
        }

        return $this->createApiResponse(
            [
                'page'             => $filter->getPage(),
                'num_pages'        => $info['num_pages'],
                'sendmail_queue'   => $data,
                'tracking_enabled' => false,
            ]
        );
    }

    //###################################################################################################################
    // get-source-info
    //###################################################################################################################

    public function getSourceInfoAction($id)
    {
        $source = $this->em->find('DeskPRO:EmailSource', $id);
        if (!$source) {
            throw $this->createNotFoundException();
        }

        $info = [];

        $info['source']     = $this->getApiData($source);
        $info['source_log'] = null;

        if ($source->log_blob) {
            try {
                $info['source_log'] = $this->container->getBlobStorage()->copyBlobRecordToString($source->log_blob);
                if ($source->log_blob->content_type === 'application/gzip') {
                    $info['source_log'] = gzdecode($info['source_log']);
                }
            } catch (\Exception $e) {
                $info['source_log'] = "Failed to read log file ({$e->getMessage()})";
            }
        }

        if ($this->in->getBool('with_raw') && $source->blob) {
            $info['source_raw'] = $this->container->getBlobStorage()->copyBlobRecordToString($source->blob);
        }

        $info['source_info'] = null;
        if ($source->source_info) {
            $info['source_info'] = $source->getSourceInfoAsString();
        }

        switch ($source->object_type) {
            case 'ticket':
                $t = $this->em->find('DeskPRO:Ticket', $source->object_id);
                if ($t) {
                    $info['ticket'] = $t->toApiData();
                }
                break;

            case 'ticket_message':
                $m = $this->em->find('DeskPRO:TicketMessage', $source->object_id);
                if ($m) {
                    $info['ticket_message'] = $m->toApiData();
                    $info['ticket']         = $m->ticket ? $m->ticket->toApiData() : null;
                }
                break;
        }

        return $this->createApiResponse($info);
    }

    //###################################################################################################################
    // get-source-summary
    //###################################################################################################################

    public function getSourceSummaryAction($id)
    {
        $source = $this->em->find('DeskPRO:EmailSource', $id);
        if (!$source) {
            throw $this->createNotFoundException();
        }

        $reader = $this->getContainer()->getEmailEzcReaderFactory()->create();
        $reader->setRawSource($this->container->getBlobStorage()->copyBlobRecordToString($source->blob));

        $info = '';

        if ($reader->getBodyHtml() && ($t = trim($reader->getBodyHtml()->getBodyUtf8()))) {
            $info .= str_repeat('#', 72);
            $info .= "\n# EMAIL HTML BODY\n";
            $info .= str_repeat('#', 72);
            $info .= "\n\n";
            $info .= $t;
            $info .= "\n\n\n\n\n";
        }
        unset($t);

        if ($reader->getBodyText() && ($t = trim($reader->getBodyText()->getBodyUtf8()))) {
            $info .= str_repeat('#', 72);
            $info .= "\n# EMAIL TEXT BODY\n";
            $info .= str_repeat('#', 72);
            $info .= "\n\n";
            $info .= $t;
        } elseif ($reader->getBodyHtml() && ($t = trim($reader->getBodyHtml()->getBodyUtf8()))) {
            $info .= str_repeat('#', 72);
            $info .= "\n# EMAIL TEXT BODY (generated based on html)\n";
            $info .= str_repeat('#', 72);
            $info .= "\n\n";
            $info .= Strings::stripTags($t);
        }
        unset($t);

        return $this->createApiResponse(['summary' => trim($info)]);
    }

    //###################################################################################################################
    // get-source-rendered
    //###################################################################################################################

    public function getSourceRenderedAction($id)
    {
        $source = $this->em->find('DeskPRO:EmailSource', $id);
        if (!$source) {
            throw $this->createNotFoundException();
        }

        $reader = $this->getContainer()->getEmailEzcReaderFactory()->create();
        $reader->setRawSource($this->container->getBlobStorage()->copyBlobRecordToString($source->blob));

        $text = null;
        $html = null;

        if ($reader->getBodyHtml() && ($t = trim($reader->getBodyHtml()->getBodyUtf8()))) {
            $html = $t;
            $html = $this->cleaner->clean($html, 'html_email_preclean');
            $html = $this->cleaner->clean($html, 'html_email_basicclean');
            $html = $this->cleaner->clean($html, 'html_email');
            $html = $this->cleaner->clean($html, 'html_email_postclean');
        }
        unset($t);

        if ($reader->getBodyText() && ($t = trim($reader->getBodyText()->getBodyUtf8()))) {
            $text = $t;
        } elseif ($reader->getBodyHtml() && ($t = trim($reader->getBodyHtml()->getBodyUtf8()))) {
            $text = Strings::stripTags($t);
        }
        unset($t);

        return $this->createApiResponse(
            [
                'text' => $text,
                'html' => $html,
            ]
        );
    }

    //###################################################################################################################
    // delete-email-source
    //###################################################################################################################

    public function deleteEmailSourceAction($id)
    {
        $source = $this->em->find('DeskPRO:EmailSource', $id);
        if (!$source) {
            throw $this->createNotFoundException();
        }

        if ($source->blob) {
            try {
                $this->container->getBlobStorage()->deleteBlobRecord($source->blob);
            } catch (\Exception $e) {
            }
        }

        if ($source->log_blob) {
            try {
                $this->container->getBlobStorage()->deleteBlobRecord($source->log_blob);
            } catch (\Exception $e) {
            }
        }

        $this->em->remove($source);
        $this->em->flush();

        return $this->createApiDeleteResponse();
    }

    //###################################################################################################################
    // reprocess-email
    //###################################################################################################################

    public function reprocessEmailSourceAction($id)
    {
        $source = $this->em->find('DeskPRO:EmailSource', $id);
        if (!$source) {
            throw $this->createNotFoundException();
        }

        $source['status']     = 'inserted';
        $source['error_code'] = null;

        $runner = new Runner();
        $runner->executeSource($source);

        return $this->createApiResponse(
            [
                'status' => $source->status,
            ]
        );
    }

    //###################################################################################################################
    // get-sendmail-info
    //###################################################################################################################

    public function getSendmailInfoAction($id)
    {
        /** @var \Application\EmailBundle\Entity\SendmailSource $sendmail */
        $sendmail = $this->em->find('EmailBundle:SendmailSource', $id);
        if (!$sendmail) {
            throw $this->createNotFoundException();
        }

        $bs = $this->container->getBlobStorage();

        $info = [];

        $info['sendmail'] = $sendmail->toArray();

        if (defined('DPC_IS_CLOUD') && !DPC_SITE_IS_APPROVED && $sendmail->getStatus() === SendmailSource::STATUS_ERROR && $sendmail->getErrorCode() === 'rate_limit') {
            // hide rate limit from unapproved acc
            $info['sendmail_log']           = null;
            $info['sendmail']['status']     = SendmailSource::STATUS_COMPLETE;
            $info['sendmail']['error_code'] = null;
        } else {
            if ($sendmail->getLogBlob()) {
                $info['sendmail_log'] = $bs->copyBlobRecordToString($sendmail->getLogBlob());
                if ($sendmail->getLogBlob()->content_type === 'application/gzip') {
                    $info['sendmail_log'] = gzdecode($info['sendmail_log']);
                }
            } else {
                $info['sendmail_log'] = null;
            }
        }

        if ($sendmail->getBlob() && $this->in->getBool('with_raw')) {
            $info['sendmail_raw'] = $bs->copyBlobRecordToString($sendmail->getBlob());
        } else {
            $info['sendmail_raw'] = null;
        }

        foreach ($sendmail->getStatuses() as $status) {
            $info['statuses'][] = $status->toArray();
        }

        return $this->createApiResponse($info);
    }

    //###################################################################################################################
    // get-sendmail-summary
    //###################################################################################################################

    public function getSendmailSummaryAction($id)
    {
        $source = $this->em->find('EmailBundle:SendmailSource', $id);
        if (!$source) {
            throw $this->createNotFoundException();
        }

        $reader = $this->getContainer()->getEmailEzcReaderFactory()->create();
        $reader->setRawSource($this->container->getBlobStorage()->copyBlobRecordToString($source->getBlob()));

        $info = '';

        if ($reader->getBodyHtml() && ($t = trim($reader->getBodyHtml()->getBodyUtf8()))) {
            $info .= str_repeat('#', 72);
            $info .= "\n# EMAIL HTML BODY\n";
            $info .= str_repeat('#', 72);
            $info .= "\n\n";
            $info .= $t;
            $info .= "\n\n\n\n\n";
        }
        unset($t);

        if ($reader->getBodyText() && ($t = trim($reader->getBodyText()->getBodyUtf8()))) {
            $info .= str_repeat('#', 72);
            $info .= "\n# EMAIL TEXT BODY\n";
            $info .= str_repeat('#', 72);
            $info .= "\n\n";
            $info .= $t;
        } elseif ($reader->getBodyHtml() && ($t = trim($reader->getBodyHtml()->getBodyUtf8()))) {
            $info .= str_repeat('#', 72);
            $info .= "\n# EMAIL TEXT BODY (generated based on html)\n";
            $info .= str_repeat('#', 72);
            $info .= "\n\n";
            $info .= Strings::stripTags($t);
        }
        unset($t);

        return $this->createApiResponse(['summary' => trim($info)]);
    }

    //###################################################################################################################
    // get-sendmail-rendered
    //###################################################################################################################

    public function getSendmailRenderedAction($id)
    {
        $source = $this->em->find('EmailBundle:SendmailSource', $id);
        if (!$source) {
            throw $this->createNotFoundException();
        }

        $reader = $this->getContainer()->getEmailEzcReaderFactory()->create();
        $reader->setRawSource($this->container->getBlobStorage()->copyBlobRecordToString($source->getBlob()));

        $text = null;
        $html = null;

        if ($reader->getBodyHtml() && ($t = trim($reader->getBodyHtml()->getBodyUtf8()))) {
            $html = $t;
            $html = $this->cleaner->clean($html, 'html_email_preclean');
            $html = $this->cleaner->clean($html, 'html_email_basicclean');
            $html = $this->cleaner->clean($html, 'html_email');
            $html = $this->cleaner->clean($html, 'html_email_postclean');
        }
        unset($t);

        if ($reader->getBodyText() && ($t = trim($reader->getBodyText()->getBodyUtf8()))) {
            $text = $t;
        } elseif ($reader->getBodyHtml() && ($t = trim($reader->getBodyHtml()->getBodyUtf8()))) {
            $text = Strings::stripTags($t);
        }
        unset($t);

        return $this->createApiResponse(
            [
                'text' => $text,
                'html' => $html,
            ]
        );
    }

    //###################################################################################################################
    // delete-sendmail-source
    //###################################################################################################################

    public function deleteSendmailAction($id)
    {
        $sendmail = $this->em->find('EmailBundle:SendmailSource', $id);
        if (!$sendmail) {
            throw $this->createNotFoundException();
        }

        if ($sendmail->getBlob()) {
            try {
                $this->container->getBlobStorage()->deleteBlobRecord($sendmail->getBlob());
            } catch (\Exception $e) {
            }
        }

        $this->em->remove($sendmail);
        $this->em->flush();

        return $this->createApiDeleteResponse();
    }

    //###################################################################################################################
    // resed-sendmail
    //###################################################################################################################

    public function resendSendmailAction($id)
    {
        $sendmail = $this->db->fetchAssoc(
            '
            SELECT *
            FROM sendmail_sources
            WHERE id = ?
        ',
            [$id]
        );
        if (!$sendmail) {
            throw $this->createNotFoundException();
        }

        if (defined('DPC_IS_CLOUD') && !DPC_SITE_IS_APPROVED) {
            // dont allow reset on unapproved sites
            // noop and pretend success
            return $this->createApiSuccessResponse();
        }

        /** @var \Application\EmailBundle\SourceMapper\SourceMapperInterface $source_mapper */
        $source_mapper = $this->get('email.source_mapper');

        $r = $source_mapper->markSourceRetry(
            $sendmail,
            sprintf('[%s] Manually marked for retry by %s', date('Y-m-d H:i:s'), $this->person->getDisplayContact())
        );

        return $this->createApiResponse(
            [
                'date_next_attempt' => $r['date_next_attempt'],
            ]
        );
    }

    //###################################################################################################################
    // sendmail-mass-actions
    //###################################################################################################################

    public function sendmailMassActionsAction($action)
    {
        $ids = $this->in->getArrayOfUInts('ids');
        if (!$ids) {
            return $this->createApiSuccessResponse();
        }

        switch ($action) {
            case 'resend':
                if (defined('DPC_IS_CLOUD') && !DPC_SITE_IS_APPROVED) {
                    // dont allow reset on unapproved sites
                    // noop and pretend success
                    return $this->createApiSuccessResponse();
                }

                /** @var \Application\EmailBundle\SourceMapper\SourceMapperInterface $source_mapper */
                $source_mapper = $this->get('email.source_mapper');

                $recs = $this->db->fetchAll(
                    '
                    SELECT *
                    FROM sendmail_sources
                    WHERE id IN (?)
                ',
                    [$ids],
                    [Connection::PARAM_INT_ARRAY]
                );

                foreach ($recs as $r) {
                    $source_mapper->markSourceRetry(
                        $r,
                        sprintf(
                            '[%s] Manually marked for retry by %s',
                            date('Y-m-d H:i:s'),
                            $this->person->getDisplayContact()
                        )
                    );
                }
                break;

            case 'abort':
                /** @var \Application\EmailBundle\SourceMapper\SourceMapperInterface $source_mapper */
                $source_mapper = $this->get('email.source_mapper');

                $recs = $this->db->fetchAll(
                    "
                    SELECT *
                    FROM sendmail_sources
                    WHERE id IN (?) AND status IN ('pending', 'inserted', 'retry')
                ",
                    [$ids],
                    [Connection::PARAM_INT_ARRAY]
                );

                foreach ($recs as $r) {
                    $source_mapper->markSourceAborted(
                        $r,
                        sprintf('[%s] Manually aborted by %s', date('Y-m-d H:i:s'), $this->person->getDisplayContact())
                    );
                }
                break;

            case 'delete':
                $bs   = $this->container->getBlobStorage();
                $recs = $this->db->fetchAll(
                    '
                    SELECT sendmail_sources.id AS sendmail_sources_id, blobs.*
                    FROM sendmail_sources
                    LEFT JOIN blobs ON blobs.id = sendmail_sources.blob_id
                    WHERE sendmail_sources.id IN ('.implode(',', $ids).')
                '
                );

                foreach ($recs as $r) {
                    $this->db->delete('sendmail_sources', ['id' => $r['sendmail_sources_id']]);
                    if ($r['id']) {
                        $bs->deleteBlobRow($r);
                    }
                }
                break;

            default:
                throw $this->createNotFoundException();
        }

        return $this->createApiSuccessResponse();
    }

    //###################################################################################################################
    // sources-mass-actions
    //###################################################################################################################

    public function emailSourceMassActionsAction($action)
    {
        $ids = $this->in->getArrayOfUInts('ids');
        if (!$ids) {
            return $this->createApiSuccessResponse();
        }

        switch ($action) {
            case 'reprocess':
                $this->db->updateIn(
                    'email_sources',
                    [
                        'status'      => 'retry',
                        'date_status' => date('Y-m-d H:i:s'),
                        'error_code'  => null,
                    ],
                    $ids
                );
                break;

            case 'delete':
                $bs   = $this->container->getBlobStorage();
                $recs = $this->db->fetchAll(
                    '
                    SELECT email_sources.id AS email_sources_id, email_sources.log_blob_id AS email_sources_log_blob_id, blobs.*
                    FROM email_sources
                    LEFT JOIN blobs ON blobs.id = email_sources.blob_id
                    WHERE email_sources.id IN ('.implode(',', $ids).')
                '
                );

                foreach ($recs as $r) {
                    if ($r['id']) {
                        $bs->deleteBlobRow($r);
                    }

                    if ($r['email_sources_log_blob_id']) {
                        $log_blob_row = $this->db->fetchAssoc(
                            'SELECT * FROM blobs WHERE id = ?',
                            [$r['email_sources_log_blob_id']]
                        );
                        if ($log_blob_row) {
                            $bs->deleteBlobRow($log_blob_row);
                        }
                    }

                    $this->db->delete('email_sources', ['id' => $r['email_sources_id']]);
                }
                break;

            default:
                throw $this->createNotFoundException();
        }

        return $this->createApiSuccessResponse();
    }
}
