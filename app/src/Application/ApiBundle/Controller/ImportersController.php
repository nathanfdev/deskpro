<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\UserTypePermission;
use Application\ImportBundle\Entity\EntityInterface;
use Application\ImportBundle\Generator\Exporter\AbstractExporter;
use Application\ImportBundle\Generator\Generator;
use Application\ImportBundle\Generator\GeneratorConfig;
use Orb\Util\OptionsArray;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImportersController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getPermissionStrategy()
    {
        return new UserTypePermission(UserTypePermission::ADMIN);
    }

    public function listAction()
    {
        /** @var Generator $generator */
        $generator = $this->get('deskpro.import.generator');
        $ret = array();
        foreach ($generator->getExporters() as $exporter) {
            if ('json' === $exporter->getType()) {
                continue;
            }

            /** @var AbstractExporter $exporter */
            $ret[] = array(
                'id' => $exporter->getType(),
                'title' => ucfirst($exporter->getType()),
                'icon' => null,
                'status' => null,
                'description' => 'blablabla',
            );
        }

        return $this->createJsonResponse($ret);
    }

    public function getAction($id)
    {
        /** @var Generator $generator */
        $generator = $this->get('deskpro.import.generator');
        $exporters = $generator->getExporters()->toArray();

        if (!isset($exporters[$id])) {
            throw new NotFoundHttpException;
        }

        $ret = array(
            'id' => $id,
            'title' => ucfirst($id),
            'icon' => null,
            'status' => null,
            'description' => 'blablabla',
        );

        return $this->createJsonResponse($ret);
    }

    public function testAction($id)
    {
        /** @var Generator $generator */
        $generator = $this->get('deskpro.import.generator');

        if (!isset($exporters[$id])) {
            throw new NotFoundHttpException;
        }

        $config = new GeneratorConfig();

        /**************/
        $import_config = new OptionsArray(dp_get_config('import', array()));
        $supported_types = array(
            EntityInterface::TYPE_TICKET,
            EntityInterface::TYPE_PERSON,
            EntityInterface::TYPE_ARTICLE,
            EntityInterface::TYPE_DOWNLOAD,
            EntityInterface::TYPE_FEEDBACK,
            EntityInterface::TYPE_NEWS,
        );
        $config
            ->setOutputPath($import_config->get('output_path'))
            ->setLogPath($import_config->get('log_path', dp_get_log_dir().'/export.log'));

        foreach ($supported_types as $type) {
            $config->addEntityType($type);
        }


        return $this->createJsonResponse(array());
    }
}
