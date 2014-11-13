<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\ApiBundle\PermissionStrategy\MultiPermissions;
use Application\ApiBundle\PermissionStrategy\PassPermission;
use Application\DeskPRO\Exception\ValidationException;

class FeedbackCategoriesController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new PassPermission(), 'listAction');

        return $multi;
    }


    ####################################################################################################################
    # list
    ####################################################################################################################

    public function listAction()
    {
        /**
         * @var \Application\DeskPRO\FeedbackCategories\FeedbackCategories $feedback_categories
         */

        $feedback_categories = $this->container->getSystemService('feedback_categories');

        return $this->createApiResponse(
            array(
                 'feedback_categories' => $feedback_categories->getAll()
            )
        );
    }

    ####################################################################################################################
    # get
    ####################################################################################################################

    public function getAction($id)
    {
        /**
         * @var \Application\DeskPRO\FeedbackCategories\FeedbackCategories $feedback_categories
         */

        $feedback_categories = $this->container->getSystemService('feedback_categories');
        $feedback_category = $feedback_categories->getById($id);

        if (!$feedback_category) {

            throw $this->createNotFoundException();
        }

        $returnedData = $this->getApiData($feedback_category);

        return $this->createApiResponse(
            array(
                 'feedback_category' => $returnedData
            )
        );
    }

    ####################################################################################################################
    # save
    ####################################################################################################################

    public function saveAction($id)
    {
        /**
         * @var \Application\DeskPRO\FeedbackCategories\FeedbackCategories $feedback_categories
         */

        $feedback_categories = $this->container->getSystemService('feedback_categories');

        if ($id) {

            $feedback_category = $feedback_categories->getById($id);

            if (!$feedback_category) {

                throw $this->createNotFoundException();
            }
        } else {

            $feedback_category = $feedback_categories->createNew();
        }

        $this->em->getConnection()->beginTransaction();

        try {

            $postData  = $this->in->getAll('post');

            // @TODO should be refactored to usage of symfony form mechanism later, this one is quite ugly

            $parent_id =
                isset($postData['feedback_category']['options']) ?
                $postData['feedback_category']['options']['parent_id'] : '';

            $feedback_category->title  = $postData['feedback_category']['title'];
            $feedback_category->parent = $feedback_categories->getParentCategory();
            $feedback_category->setOption('parent_id', $parent_id);

            $this->em->persist($feedback_category);
            $this->em->flush();

            $this->em->getConnection()->commit();

        } catch (\Exception $e) {

            $this->em->getConnection()->rollback();
            throw $e;
        }

        return $this->createApiResponse(
            array(
                 'success' => true,
                 'id'      => $feedback_category->getId(),
            )
        );
    }

    ####################################################################################################################
    # remove
    ####################################################################################################################

    public function removeAction($id)
    {
        /**
         * @var \Application\DeskPRO\FeedbackCategories\FeedbackCategories $feedback_categories
         */

        $feedback_categories = $this->container->getSystemService('feedback_categories');
        $feedback_category   = $feedback_categories->getById($id);

        if (!$feedback_category) {

            throw $this->createNotFoundException();
        }

        $move_to                   = $this->in->getUint('move_to');
        $move_to_feedback_category = $feedback_categories->getById($move_to);

        $skip_moving = false;

        if (!$move_to_feedback_category) {

            $skip_moving = true;
        }

        if (!$skip_moving && $move_to_feedback_category->getId() == $feedback_category->getId()) {

            throw ValidationException::create(
                "feedback_type.remove.move_feedback_categories",
                "You must choose a different feedback category"
            );
        }

        $old_id = $feedback_category->getId();

        $this->db->beginTransaction();

        try {

            if(!$skip_moving) {

                $this->db->executeUpdate(
                    "UPDATE custom_data_feedback SET field_id = ? WHERE field_id = ?",
                    array($move_to, $old_id)
                );
            }

            $this->em->remove($feedback_category);
            $this->em->flush();

            $this->db->commit();

        } catch(\Exception $e) {

            $this->db->rollback();
            throw $e;
        }

        return $this->createSuccessResponse(array('old_id' => $old_id));
    }

    ####################################################################################################################
    # save-display-order
    ####################################################################################################################

    public function saveDisplayOrderAction()
    {
        $display_orders = $this->in->getArrayOfUInts('display_orders');

        /**
         * @var \Application\DeskPRO\FeedbackCategories\FeedbackCategories $feedback_categories
         */

        $feedback_categories = $this->container->getSystemService('feedback_categories');
        $feedback_categories->updateDisplayOrders($display_orders);

        return $this->createSuccessResponse();
    }
}
