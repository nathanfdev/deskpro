<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\ActionEngine\Utils;

use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\ActionInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\ActionApplicatorInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Exception\ActionApplicatorDoesNotExists;
use Doctrine\ORM\EntityManager;

class ActionTransformer
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Takes an ActionInterface and returns a serialized form in JSON format.
     *
     * @param ActionInterface $action
     *
     * @return string
     */
    public function toJson(ActionInterface $action)
    {
        $serialized = $this->actionToArray($action);

        return json_encode($serialized);
    }

    /**
     * Takes a JSON string produced by toJson and returns the ActionInterface.
     *
     * @param string $namespace
     * @param string $json
     *
     * @return ActionApplicatorInterface
     */
    public function toActionApplicator($namespace, $json)
    {
        $serialized_array = json_decode($json, true);

        return $this->arrayToActionApplicator($namespace, $serialized_array);
    }

    /**
     * @param ActionInterface $action
     *
     * @return array
     */
    public function actionToArray(ActionInterface $action)
    {
        return array_merge(
            ['type' => ActionTypeCodes::getActionTypeCode($action)],
            ['options' => $action->serialize()]
        );
    }

    /**
     * @param string $namespace
     * @param array  $serialized_array
     *
     * @return ActionApplicatorInterface
     */
    public function arrayToActionApplicator($namespace, array $serialized_array)
    {
        $class = ActionTypeCodes::getActionApplicatorClassForTypeCode($namespace, $serialized_array['type']);
        if (!class_exists($class)) {
            throw new ActionApplicatorDoesNotExists($serialized_array['type']);
        }
        $options = array_key_exists('options', $serialized_array) ? $serialized_array['options'] : [];

        return new $class($this->em, $options);
    }

    /**
     * @param string          $namespace
     * @param ActionInterface $action
     *
     * @return ActionApplicatorInterface
     */
    public function actionToApplicator($namespace, ActionInterface $action)
    {
        $serialized = $action->serialize();
        $type       = ActionTypeCodes::getActionTypeCode($action);
        $class      = ActionTypeCodes::getActionApplicatorClassForTypeCode($namespace, $type);
        if (!class_exists($class)) {
            throw new ActionApplicatorDoesNotExists($type);
        }
        $options = array_key_exists('options', $serialized) ? $serialized['options'] : [];

        return new $class($this->em, $options);
    }
}
