<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\Entity\ApiKeyLog;
use Application\DeskPRO\Form\Type\ApiKeyType;
use Application\LegacyApiBundle\HttpFoundation\JsonResponse;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Limits\Model\AbstractLimit;
use DeskPRO\Bundle\AppBundle\Limits\Model\LimitInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @ApiModes("all")
 */
class ApiKeysController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new PassPermission(), 'getAgentsForKeyAction');

        return $multi;
    }

    //##################################################################################################################
    // list
    //###################################################################################################################

    /**
     * SWG\Api(
     *    path="/api_keys",
     *    SWG\Operation(
     *        method="GET",
     *        summary="Get list of all existing API Keys",
     *        notes="Returns array of all existing API Keys"
     *    )
     * ).
     */
    public function listAction()
    {
        $keys = $this->em->getRepository(ApiKey::class)->findAll();
        $data = [];
        foreach ($keys as $index => $key) {
            /* @var ApiKey $key */
            $data[$index] = $key->toApiData(false);
            $this->getLimitsData($data[$index], $key);
        }

        return $this->createApiResponse($data);
    }

    //##################################################################################################################
    // get
    //###################################################################################################################

    /**
     * SWG\Api(
     *    path="/api_keys/{id}",
     *    SWG\Operation(
     *        method="GET",
     *        summary="Find API Key By ID",
     *        notes="Returns API Key based on ID",
     *        SWG\Parameter(
     *            name="id",
     *            description="ID of API Key that needs to be fetched",
     *            required=true,
     *            type="integer",
     *            paramType="path"
     *        ),
     *        SWG\ResponseMessage(code=404, message="API Key not found")
     *    )
     * ).
     */
    public function getAction($id)
    {
        if (!$key = $this->em->find(ApiKey::class, $id)) {
            throw $this->createNotFoundException();
        }

        $data = $this->getApiData($key);
        $this->getLimitsData($data, $key);

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

    /**
     * @param   $id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveAction($id)
    {
        /* @var $key ApiKey */
        if ($id) {
            if (!$key = $this->em->find(ApiKey::class, $id)) {
                throw $this->createNotFoundException();
            }
        } else {
            $key = new ApiKey();
            $this->em->persist($key);
        }

        $data = $this->in->getAll('req');

        $form = $this->createForm(new ApiKeyType(), $key);
        $form->submit($data);

        if ($form->isValid()) {
            $this->em->flush();

            $limits_service = $this->get('api_limits.limits_service');
            $limits         = $limits_service->getKeyLimits($key);

            /** @var LimitInterface[] $limits_data */
            $limits_data = [
                AbstractLimit::INTERVAL_DAY  => null,
                AbstractLimit::INTERVAL_HOUR => null,
            ];

            foreach ($limits as $limit) {
                $limits_data[$limit->getIntervalInSeconds()] = $limit;
            }

            foreach ($limits_data as $interval => $limit) {
                if (!$limit) {
                    $limits_data[$interval] = $limits_service->createLimit($interval);
                }
            }

            // set hourly and daily limits
            // if hour/daily settings are set to '-1' then it means that it's unlimited value
            $settings = $this->get('settings_resolver')->getDefaultSettings();

            $getSettingValue = function ($queryParam, $settingName) use ($settings) {
                $value        = $this->in->getInt($queryParam, 'req');
                $settingValue = $settings->get("api_limits.key.$settingName");

                // prevent setting unbound values
                if ($settingValue > 0) {
                    $value = min($value, $settingValue);
                    if ($value < 0) {
                        $value = $settingValue;
                    }
                }

                $value = max(-1, $value);

                return $value;
            };

            $hourlyLimit = $getSettingValue('hourly_limit', 'hour');
            $dailyLimit  = $getSettingValue('daily_limit', 'day');

            $limits_data[AbstractLimit::INTERVAL_HOUR]->setLimit($hourlyLimit);
            $limits_data[AbstractLimit::INTERVAL_HOUR]->setCurrent($hourlyLimit);
            $limits_data[AbstractLimit::INTERVAL_DAY]->setLimit($dailyLimit);
            $limits_data[AbstractLimit::INTERVAL_DAY]->setCurrent($dailyLimit);

            foreach ($limits_data as $limit) {
                $limits_service->saveLimit($key, $limit);
            }
        } else {
            return $this->createApiErrorInfoResponse('validation_error', $this->getFormValidationErrorsString($form), []);
        }

        return $this->getAction($key['id']);
    }

    //###################################################################################################################
    // remove
    //###################################################################################################################

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction($id)
    {
        /** @var $key ApiKey */
        if (!$key = $this->em->find(ApiKey::class, $id)) {
            throw $this->createNotFoundException();
        }

        $old_id = $key['id'];

        $this->em->remove($key);
        $this->em->flush();

        return $this->createSuccessResponse(['old_id' => $old_id]);
    }

    //###################################################################################################################
    // get-logs
    //###################################################################################################################

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getLogsAction($id)
    {
        /** @var $key ApiKey */
        if (!$key = $this->em->find(ApiKey::class, $id)) {
            throw $this->createNotFoundException();
        }

        $logs = $this->em->getRepository(ApiKeyLog::class)->getLogsForKey($key);

        $logs = $this->getApiData($logs);

        return $this->createSuccessResponse([
            'logs' => $logs,
        ]);
    }

    //###################################################################################################################
    // regenerate
    //###################################################################################################################

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function regenerateAction($id)
    {
        /** @var $key ApiKey */
        if (!$key = $this->em->find(ApiKey::class, $id)) {
            throw $this->createNotFoundException();
        }

        $key->regenerateApiKey();
        $this->em->flush();

        return $this->createSuccessResponse(['code' => $key['code'], 'keyString' => $key['keyString']]);
    }

    /**
     * @param $logEntryId
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return JsonResponse
     */
    public function replayLogEntryAction($logEntryId)
    {
        /** @var $entry \Application\DeskPRO\Entity\ApiKeyLog */
        if (!$entry = $this->em->find(ApiKeyLog::class, $logEntryId)) {
            throw new NotFoundHttpException();
        }
        /** @var ApiKey $key */
        $key     = $entry->key;
        $request = $entry['request'];

        $api  = new \DeskPRO\Api($this->container->getBrandSetting('core.deskpro_url'), $key->getKeyString(), $key->person['id']);
        $path = 0 === strpos($request['path'], '/api') ? substr($request['path'], 4) : $request['path'];
        /** @var \DeskPRO\Api\Result $response */
        $response = $api->call($request['method'], $path, $request['payload']);

        $result = [
            'status'  => $response->getResponseCode(),
            'content' => $response->getData(),
        ];

        return $this->createApiResponse($result);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDefaultSettingsAction()
    {
        $settings_resolver = $this->get('settings_resolver');

        return $this->createApiResponse([
            'hourly_limit' => $settings_resolver->getDefaultSettings()->get('api_limits.key.hour'),
            'daily_limit'  => $settings_resolver->getDefaultSettings()->get('api_limits.key.day'),
        ]);
    }

    /**
     * @param array  $data
     * @param ApiKey $key
     */
    protected function getLimitsData(array &$data, ApiKey $key)
    {
        $service = $this->get('api_limits.limits_service');
        foreach ($service->getKeyLimits($key) as $limit) {
            if ($limit->getIntervalInSeconds() === AbstractLimit::INTERVAL_HOUR) {
                $data['hourly_limit'] = $limit->getLimit();
            } elseif ($limit->getIntervalInSeconds() === AbstractLimit::INTERVAL_DAY) {
                $data['daily_limit'] = $limit->getLimit();
            }
        }
    }
}
