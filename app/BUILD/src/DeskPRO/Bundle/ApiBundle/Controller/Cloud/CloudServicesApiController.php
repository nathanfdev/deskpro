<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Cloud;

use Application\DeskPRO\Entity\TmpData;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiController.
 *
 * @ApiModes({"master_key", "key"})
 * @ApiUserContext("open")
 * @Rest\Route("/cloud-services")
 */
class CloudServicesApiController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        if (!defined('DPC_IS_CLOUD')) {
            exit;
        }
    }

    /**
     * @Rest\Get("/get-settings/{ids}")
     */
    public function getSettingsAction($ids)
    {
        $ids = array_map('trim', explode(',', $ids));

        if (empty($ids)) {
            return [];
        }

        $ret = [];
        foreach ($ids as $id) {
            $ret[$id] = $this->get('settings_resolver')->getGlobalSettings()->get($id, null);
        }

        return $ret;
    }

    /**
     * @Rest\Get("/agents")
     */
    public function agentsListAction()
    {
        $agents = $this->get('database_connection')->fetchAll('
            SELECT people.id, people.secret_string, people.salt, people.name, people.can_admin, people.can_reports, people.can_billing, people_emails.email, people.can_admin
			FROM people
			LEFT JOIN people_emails ON people_emails.id = people.primary_email_id
			WHERE people.is_agent = 1 AND people.is_deleted = 0
			ORDER BY id ASC
        ');

        return $agents;
    }

    /**
     * @param string $id
     * @param string $auth
     * @Rest\Get("/get-tmpdata/{id}/{auth}")
     *
     * @return array|null
     */
    public function tmpDataAction($id, $auth)
    {
        /** @var TmpData $tmpdata */
        $tmpdata = $this->getRepository(TmpData::class)->findOneBy(['id' => $id, 'auth' => $auth]);

        if (isset($_REQUEST['touch'])) {
            $tmpdata->setDateExpire(new \DateTime('+15 minutes'));
            $this->get('doctrine.orm.default_entity_manager')->persist($tmpdata);
            $this->get('doctrine.orm.default_entity_manager')->flush();
        }

        return $tmpdata ? $tmpdata->toApiData(true) : null;
    }

    /**
     * @Rest\Post("/save-tmpdata")
     *
     * @param Request $request
     *
     * @return array|null
     */
    public function saveTmpDataAction(Request $request)
    {
        $input   = json_decode($request->getContent(), true);
        $tmpdata = TmpData::create(
            @$input['type'] ?: 'UNSET',
            @$input['data'] ?: [],
            @$input['expire'] ?: null
        );
        $this->get('doctrine.orm.default_entity_manager')->persist($tmpdata);
        $this->get('doctrine.orm.default_entity_manager')->flush();

        return $tmpdata->toApiData(true);
    }

    /**
     * @Rest\Post("/requeue-rate-limited-email")
     */
    public function requeueRateLimitedEmailAction()
    {
        /** @var \Application\EmailBundle\SourceMapper\SourceMapperInterface $source_mapper */
        $source_mapper = $this->get('email.source_mapper');

        $sendmails = $this->get('database_connection')->fetchAssoc("
            SELECT *
            FROM sendmail_sources
            WHERE status = 'rate_limit'
            ORDER BY id ASC
        ");

        foreach ($sendmails as $sendmail) {
            try {
                $source_mapper->markSourceRetry(
                    $sendmail,
                    sprintf('[%s] Marked for retry by system', date('Y-m-d H:i:s'))
                );
            } catch (\Exception $e) {
            }
        }

        return ['count' => count($sendmails)];
    }
}
