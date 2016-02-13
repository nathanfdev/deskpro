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

################################################################################
# Feedback
################################################################################

$em->getConnection()->executeUpdate(
    "
      INSERT INTO `feedback` (`id`, `status_category_id`, `category_id`, `person_id`, `language_id`, `hidden_status`, `is_reviewed`, `popularity`, `slug`, `title`, `content`, `view_count`, `total_rating`, `num_comments`, `num_ratings`, `status`, `date_created`, `date_published`, `date_updated`, `date_last_comment`)
        VALUES
            (1, 5, 1, 1, NULL, NULL, 1, 0, '_slug-to-feedback-1', 'Test feedback 1', 'Content of test feedback 1. This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 1, 0, 2, 'closed', '2015-09-13 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
            (2, 5, 1, 1, NULL, NULL, 1, 0, '_slug-to-feedback-2', 'Test feedback 2', 'Content of test feedback 2', 0, 3, 0, 4, 'closed', '2015-09-14 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (3, 5, 2, 1, NULL, NULL, 1, 0, '_slug-to-feedback-3', 'Test feedback 3', 'Content of test feedback 3', 0, 5, 0, 6, 'closed', '2015-09-15 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (4, 5, 3, 1, NULL, NULL, 1, 0, '_slug-to-feedback-4', 'Test feedback 4', 'Content of test feedback 4', 0, 0, 0, 0, 'closed', '2015-09-16 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (5, 5, 1, 1, NULL, NULL, 1, 0, '_slug-to-feedback-5', 'Test feedback 5', 'Content of test feedback 5', 0, 1, 0, 1, 'closed', '2015-09-17 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (6, 5, 1, 1, NULL, NULL, 1, 0, '_slug-to-feedback-6', 'Test feedback 6', 'Content of test feedback 6', 0, 2, 0, 1, 'closed', '2015-09-18 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (7, 5, 2, 1, NULL, NULL, 1, 15, '_slug-to-feedback-7', 'Test feedback 7', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'closed', '2015-09-19 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (8, 5, 1, 1, NULL, NULL, 1, 0, '_slug-to-feedback-8', 'Test feedback 8', 'This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 0, 0, 2, 'closed', '2015-09-19 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
            (9, 5, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-9', 'Test feedback 9', 'Content of test feedback 9. This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 1, 0, 2, 'closed', '2015-09-20 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
            (10, 5, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-10', 'Test feedback 10', 'Content of test feedback 10', 0, 3, 0, 4, 'closed', '2015-09-21 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (11, 6, 2, 1, NULL, NULL, 1, 0, 'slug-to-feedback-11', 'Test feedback 11', 'Content of test feedback 11', 0, 5, 0, 6, 'closed', '2015-09-22 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (12, 6, 3, 1, NULL, NULL, 1, 0, 'slug-to-feedback-12', 'Test feedback 12', 'Content of test feedback 12', 0, 0, 0, 0, 'closed', '2015-09-23 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (13, 6, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-13', 'Test feedback 13', 'Content of test feedback 13', 0, 1, 0, 1, 'closed', '2015-09-24 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (14, 6, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-14', 'Test feedback 14', 'Content of test feedback 14', 0, 2, 0, 1, 'closed', '2015-09-25 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (15, 6, 2, 1, NULL, NULL, 1, 15, 'slug-to-feedback-15', 'Test feedback 15', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'closed', '2015-09-26 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (16, 6, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-16', 'Test feedback 16', 'This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 0, 0, 2, 'closed', '2015-09-27 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
            (17, 6, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-17', 'Test feedback 17', 'Content of test feedback 17', 0, 3, 0, 4, 'closed', '2015-09-27 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (18, 6, 2, 1, NULL, NULL, 1, 0, 'slug-to-feedback-18', 'Test feedback 18', 'Content of test feedback 18', 0, 5, 0, 6, 'closed', '2015-09-28 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (19, 6, 3, 1, NULL, NULL, 1, 0, 'slug-to-feedback-19', 'Test feedback 19', 'Content of test feedback 3', 0, 0, 0, 0, 'closed', '2015-09-29 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (20, 6, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-20', 'Test feedback 20', 'Content of test feedback 20', 0, 1, 0, 1, 'closed', '2015-09-30 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (21, 1, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-21', 'Test feedback 21', 'Content of test feedback 21', 0, 2, 0, 1, 'active', '2015-10-01 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (22, 1, 2, 1, NULL, NULL, 1, 15, 'slug-to-feedback-22', 'Test feedback 22', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'active', '2015-10-02 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (23, 1, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-23', 'Test feedback 23', 'Content of test feedback 23. This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 1, 0, 2, 'active', '2015-10-02 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
            (24, NULL, 1, 1, NULL, 'deleted', 0, 0, 'slug-to-feedback-24', 'Test feedback 24', 'Content of test feedback 24', 0, 3, 0, 4, 'hidden', '2015-10-03 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (25, 1, 2, 1, NULL, NULL, 0, 0, 'slug-to-feedback-25', 'Test feedback 25', 'Content of test feedback 25', 0, 5, 0, 6, 'active', '2015-10-04 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (26, 1, 3, 1, NULL, NULL, 1, 0, 'slug-to-feedback-26', 'Test feedback 26', 'Content of test feedback 26', 0, 0, 0, 0, 'active', '2015-10-05 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (27, NULL, 1, 1, NULL, 'spam', 0, 0, 'slug-to-feedback-27', 'Test feedback 27', 'Content of test feedback 27', 0, 1, 0, 1, 'hidden', '2015-10-06 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (28, 1, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-28', 'Test feedback 28', 'Content of test feedback 28', 0, 2, 0, 1, 'active', '2015-10-07 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (29, 1, 2, 1, NULL, NULL, 1, 15, 'slug-to-feedback-29', 'Test feedback 29', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'active', '2015-10-08 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (30, 1, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-30', 'Test feedback 30', 'This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 0, 0, 2, 'active', '2015-04-13 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
            (31, NULL, 1, 1, NULL, 'deleted', 0, 0, 'slug-to-feedback-31', 'Test feedback 31', 'Content of test feedback 31', 0, 3, 0, 4, 'hidden', '2015-10-09 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (32, 2, 2, 1, NULL, NULL, 0, 0, 'slug-to-feedback-32', 'Test feedback 32', 'Content of test feedback 32', 0, 5, 0, 6, 'active', '2015-10-10 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (33, 2, 3, 1, NULL, NULL, 1, 0, 'slug-to-feedback-33', 'Test feedback 33', 'Content of test feedback 33', 0, 0, 0, 0, 'active', '2015-10-11 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (34, NULL, 1, 1, NULL, 'spam', 0, 0, 'slug-to-feedback-34', 'Test feedback 34', 'Content of test feedback 34', 0, 1, 0, 1, 'hidden', '2015-10-12 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (35, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-35', 'Test feedback 35', 'Content of test feedback 35', 0, 2, 0, 1, 'active', '2015-10-13 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (36, 2, 2, 1, NULL, NULL, 1, 15, 'slug-to-feedback-36', 'Test feedback 36', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'active', '2015-10-14 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (37, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-37', 'Test feedback 37', 'Content of test feedback 37. This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 1, 0, 2, 'active', '2015-10-14 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
            (38, NULL, 1, 1, NULL, 'deleted', 0, 0, 'slug-to-feedback-38', 'Test feedback 38', 'Content of test feedback 38', 0, 3, 0, 4, 'hidden', '2015-10-15 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (39, 2, 2, 1, NULL, NULL, 0, 0, 'slug-to-feedback-39', 'Test feedback 39', 'Content of test feedback 39', 0, 5, 0, 6, 'active', '2015-06-02 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (40, 2, 3, 1, NULL, NULL, 1, 0, 'slug-to-feedback-40', 'Test feedback 40', 'Content of test feedback 40', 0, 0, 0, 0, 'active', '2015-10-16 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (41, NULL, 1, 1, NULL, 'spam', 0, 0, 'slug-to-feedback-41', 'Test feedback 41', 'Content of test feedback 41', 0, 1, 0, 1, 'hidden', '2015-10-17 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (42, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-42', 'Test feedback 42', 'Content of test feedback 42', 0, 2, 0, 1, 'active', '2015-10-18 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (43, 2, 2, 1, NULL, NULL, 1, 15, 'slug-to-feedback-43', 'Test feedback 43', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'active', '2015-10-20 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (44, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-44', 'Test feedback 44', 'This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 0, 0, 2, 'active', '2015-10-21 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
            (45, NULL, 1, 1, NULL, 'deleted', 0, 0, 'slug-to-feedback-45', 'Test feedback 45', 'Content of test feedback 45', 0, 3, 0, 4, 'hidden', '2015-10-22 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (46, 2, 2, 1, NULL, NULL, 0, 0, 'slug-to-feedback-46', 'Test feedback 46', 'Content of test feedback 46', 0, 5, 0, 6, 'active', '2015-10-23 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (47, 2, 3, 1, NULL, NULL, 1, 0, 'slug-to-feedback-47', 'Test feedback 47', 'Content of test feedback 47', 0, 0, 0, 0, 'active', '2015-10-24 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (48, NULL, 1, 1, NULL, 'spam', 0, 0, 'slug-to-feedback-48', 'Test feedback 48', 'Content of test feedback 48', 0, 1, 0, 1, 'hidden', '2015-10-25 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (49, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-49', 'Test feedback 49', 'Content of test feedback 49', 0, 2, 0, 1, 'active', '2015-10-26 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (50, 2, 2, 1, NULL, NULL, 1, 15, 'slug-to-feedback-50', 'Test feedback 50', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'active', '2015-10-26 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (51, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-51', 'Test feedback 51', 'Content of test feedback 51. This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 1, 0, 2, 'active', '2015-10-28 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
            (52, NULL, 1, 1, NULL, 'deleted', 0, 0, 'slug-to-feedback-52', 'Test feedback 52', 'Content of test feedback 52', 0, 3, 0, 4, 'hidden', '2015-10-29 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (53, 2, 2, 1, NULL, NULL, 0, 0, 'slug-to-feedback-53', 'Test feedback 53', 'Content of test feedback 53', 0, 5, 0, 6, 'active', '2015-10-30 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (54, 2, 3, 1, NULL, NULL, 1, 0, 'slug-to-feedback-54', 'Test feedback 54', 'Content of test feedback 54', 0, 0, 0, 0, 'active', '2015-10-31 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (55, NULL, 1, 1, NULL, 'spam', 0, 0, 'slug-to-feedback-55', 'Test feedback 55', 'Content of test feedback 55', 0, 1, 0, 1, 'hidden', '2015-11-01 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (56, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-56', 'Test feedback 56', 'Content of test feedback 56', 0, 2, 0, 1, 'active', '2015-11-02 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (57, 2, 2, 1, NULL, NULL, 1, 15, 'slug-to-feedback-57', 'Test feedback 57', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'active', '2015-11-02 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (58, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-58', 'Test feedback 58', 'This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 0, 0, 2, 'active', '2015-04-13 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
            (59, NULL, 1, 1, NULL, 'deleted', 0, 0, 'slug-to-feedback-59', 'Test feedback 59', 'Content of test feedback 59', 0, 3, 0, 4, 'hidden', '2015-11-04 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (60, 2, 2, 1, NULL, NULL, 0, 0, 'slug-to-feedback-60', 'Test feedback 60', 'Content of test feedback 60', 0, 5, 0, 6, 'active', '2015-11-05 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (61, 2, 3, 1, NULL, NULL, 1, 0, 'slug-to-feedback-61', 'Test feedback 61', 'Content of test feedback 61', 0, 0, 0, 0, 'active', '2015-11-06 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (62, NULL, 1, 1, NULL, 'spam', 0, 0, 'slug-to-feedback-62', 'Test feedback 62', 'Content of test feedback 62', 0, 1, 0, 1, 'hidden', '2015-11-07 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (63, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-63', 'Test feedback 63', 'Content of test feedback 63', 0, 2, 0, 1, 'active', '2015-11-08 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
            (64, 2, 2, 1, NULL, NULL, 1, 15, 'slug-to-feedback-64', 'Test feedback 64', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'active', '2015-11-08 00:00:00', NULL, '0000-00-00 00:00:00', NULL);
    "
);

$em->getConnection()->executeUpdate(
    "
      INSERT INTO `labels_feedback` (`feedback_id`, `label`)
        VALUES
          (1, 'First feedback label'),
          (1, 'Second feedback label'),
          (2, 'First feedback label'),
          (3, 'Third feedback label');
"
);

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `custom_data_feedback` (`id`, `feedback_id`, `field_id`, `root_field_id`, `value`, `input`)
      VALUES
        (1, 1, 1, NULL, 0, 'Windows'),
        (2, 2, 1, NULL, 0, 'Linux'),
        (3, 3, 1, NULL, 0, 'Linux'),
        (4, 4, 1, NULL, 0, 'Mac');
"
);

################################################################################
# TEMPORARY TEST DATA: People
################################################################################

if (!function_exists('create_user')) {
    function create_user($fname, $lname, $email, $pass, $agent = false, $admin = false, $is_deleted = false)
    {
        $user             = new \Application\DeskPRO\Entity\Person();
        $user->first_name = $fname;
        $user->last_name  = $lname;
        $user->setEmail($email, true);
        $user->setPassword($pass);
        $user->is_user      = true;
        $user->is_confirmed = true;
        $user->is_deleted   = $is_deleted;

        if ($agent || $admin) {
            $user->is_agent  = true;
            $user->can_agent = true;
        }

        if ($admin) {
            $user->can_admin   = true;
            $user->can_billing = true;
            $user->can_reports = true;
        }

        return $user;
    }
}

$em->persist(create_user('John', 'Doe', 'john@doe.lo', '11111111', true, true));
$em->persist(create_user('Jane', 'Doe', 'jane@doe.lo', '11111111', true, false));
$em->persist(create_user('Jack', 'Doe', 'jack@doe.lo', '11111111', false, false));
$em->persist(create_user('Friedrich', 'Doe', 'fred@doe.lo', '11111111', true, false, true));
$em->flush();

################################################################################
# TEMPORARY TEST DATA: Organizations
################################################################################

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `organizations`
        (`picture_blob_id`, `name`, `summary`, `importance`, `date_created`)
    VALUES
        (NULL, 'Organization 1', 'test organization', 1, '2015-08-03 00:00:00'),
        (NULL, 'Organization 2', 'test organization', 2, '2015-08-07 00:00:00')
    ;
"
);

################################################################################
# TEMPORARY TEST DATA: Groups
################################################################################

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `usergroups`
        (`title`, `note`, `is_agent_group`, `sys_name`, `is_enabled`)
    VALUES
        ('Group 1', 'test', 0, 'g1', 1),
        ('Group 2 (disabled)', 'test', 0, 'g2', 0),
        ('Group 3', 'test', 0, 'g3', 1),
        ('Group 4', 'test', 0, 'g4', 1)
    ;

    INSERT INTO `person2usergroups`
        (`person_id`, `usergroup_id`)
    VALUES
        (1, 1),
        (1, 2),
        (2, 2),
        (2, 3),
        (3, 3),
        (4, 4)
    ;
"
);

################################################################################
# TEMPORARY TEST DATA: Chats
################################################################################

//$em->getConnection()->executeUpdate(
//    "
//    INSERT INTO `chat_conversations`
//        (`department_id`, `agent_id`, `subject`, `status`, `person_name`, `person_email`, `rating_comment`,
//         `is_agent`, `is_window`, `date_created`, `should_send_transcript`, `total_to_ended`, `ended_by`)
//
//    VALUES
//
//        (1, 1, 'Test chat 1', 'test', 'test', 'test', '', 1, 1, '2010-08-01 10:19:00', 1, 1, 'test'),
//        (1, 1, 'Test chat 2', 'test', 'test', 'test', '', 1, 1, '2011-08-02 10:19:00', 1, 1, 'test'),
//        (1, 2, 'Test chat 3', 'test', 'test', 'test', '', 1, 1, '2015-08-03 10:19:00', 1, 1, 'test'),
//        (2, 2, 'Test chat 4', 'test', 'test', 'test', '', 1, 1, '2015-08-04 10:19:00', 1, 1, 'test'),
//        (2, 2, 'Test chat 5', 'test', 'test', 'test', '', 1, 1, '2015-08-05 10:19:00', 1, 1, 'test')
//    ;
//"
//);

################################################################################
# TEMPORARY TEST DATA: Articles, News, Downloads and their Categories
################################################################################

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `articles`
        (`id`, `person_id`, `slug`, `title`, `content`, `view_count`, `total_rating`, `num_comments`,
         `num_ratings`, `status`, `hidden_status`, `date_created`, `date_published`, `date_updated`)
    VALUES
        (2, 1, '2', 'Test Article #2', 'Test Article #2', 0, 0, 0, 0, 'published', NULL, '2011-08-03 00:00:00', NULL, NULL),
        (3, 2, '3', 'Test Article #3', 'Test Article #3', 0, 0, 0, 0, 'published', NULL, '2011-08-05 00:00:00', NULL, '2012-08-10 00:00:00'),
        (4, 2, '4', 'Test Article #4', 'Test Article #4', 0, 0, 0, 0, 'archived', NULL, '2011-08-04 00:00:00', NULL, '2012-08-06 00:00:00'),
        (5, 2, '5', 'Test Article #5', 'Test Article #5', 0, 0, 0, 0, 'hidden', 'draft', '2011-08-11 00:00:00', NULL, NULL),
        (6, 3, '6', 'Test Article #6', 'Test Article #6', 0, 0, 0, 0, 'published', NULL, '2011-08-12 00:00:00', NULL, '2012-08-13 00:00:00'),
        (7, 3, '7', 'Test Article #7', 'Test Article #7', 0, 0, 0, 0, 'published', NULL, '2011-08-13 00:00:00', NULL, NULL),
        (8, 1, '8', 'Test Article #8', 'Test Article #8', 0, 0, 0, 0, 'published', NULL, '2011-08-02 00:00:00', NULL, '2012-03-03 00:00:00')
    ;

    INSERT INTO `article_categories`
        (`id`, `parent_id`, `is_agent`, `is_book`, `template_suffix`, `title`, `slug`, `display_order`, `depth`)
    VALUES
        (1, NULL, 1, 1, NULL, 'Test Category #1', '1', 1, 1),
        (2, NULL, 0, 0, NULL, 'Test Category #2', '2', 2, 1),
        (3, 1, 1, 1, NULL, 'Test Category #3', '3', 1, 1),
        (4, 1, 1, 1, NULL, 'Test Category #4', '4', 1, 1),
        (5, 2, 1, 1, NULL, 'Test Category #5', '5', 1, 1),
        (6, 2, 1, 1, NULL, 'Test Category #6', '6', 1, 1),
        (7, 3, 1, 1, NULL, 'Test Category #7', '7', 1, 1),
        (8, 3, 1, 1, NULL, 'Test Category #8', '8', 1, 1),
        (9, 7, 1, 1, NULL, 'Test Category #9', '9', 1, 1),
        (10, 7, 1, 1, NULL, 'Test Category #10', '10', 1, 1),
        (11, 7, 1, 1, NULL, 'Test Category #11', '11', 1, 1)
    ;

    INSERT INTO `article_to_categories`
        (`article_id`, `category_id`)
    VALUES
        (2, 1),
        (3, 1),
        (3, 2),
        (4, 2),
        (5, 1),
        (5, 2),
        (6, 1),
        (7, 1),
        (8, 1),
        (8, 9)
    ;

    INSERT INTO `news_categories`
        (`id`, `parent_id`, `title`, `slug`, `display_order`, `depth`)
    VALUES
        (1, NULL, 'Test Category #1', '1', 1, 1),
        (2, NULL, 'Test Category #2', '2', 2, 1),
        (3, 1, 'Test Category #3', '3', 2, 1),
        (4, 1, 'Test Category #4', '4', 2, 1),
        (5, 2, 'Test Category #5', '5', 2, 1),
        (6, 2, 'Test Category #6', '6', 2, 1),
        (7, 3, 'Test Category #7', '7', 2, 1),
        (8, 3, 'Test Category #8', '8', 2, 1),
        (9, 7, 'Test Category #9', '9', 2, 1),
        (10, 7, 'Test Category #10', '10', 2, 1),
        (11, 7, 'Test Category #11', '11', 2, 1)
    ;

    INSERT INTO `news`
        (`id`, `category_id`, `person_id`, `slug`, `title`, `content`, `view_count`, `total_rating`, `num_comments`,
         `num_ratings`, `status`, `hidden_status`, `date_created`, `date_published`, `date_updated`)
    VALUES
        (1, 1, 1, '1', 'Test News #1', 'Test News #1', 0, 0, 0, 0, 'published', NULL, '2011-08-02 00:00:00', NULL, '2012-03-03 00:00:00'),
        (2, 1, 1, '2', 'Test News #2', 'Test News #2', 0, 0, 0, 0, 'published', NULL, '2011-08-03 00:00:00', NULL, NULL),
        (3, 1, 2, '3', 'Test News #3', 'Test News #3', 0, 0, 0, 0, 'published', NULL, '2011-08-05 00:00:00', NULL, '2012-08-10 00:00:00'),
        (4, 1, 2, '4', 'Test News #4', 'Test News #4', 0, 0, 0, 0, 'archived', NULL, '2011-08-04 00:00:00', NULL, '2012-08-06 00:00:00'),
        (5, 1, 2, '5', 'Test News #5', 'Test News #5', 0, 0, 0, 0, 'hidden', 'draft', '2011-08-11 00:00:00', NULL, NULL),
        (6, 1, 3, '6', 'Test News #6', 'Test News #6', 0, 0, 0, 0, 'published', NULL, '2011-08-12 00:00:00', NULL, '2012-08-13 00:00:00'),
        (7, 2, 3, '7', 'Test News #7', 'Test News #7', 0, 0, 0, 0, 'published', NULL, '2011-08-13 00:00:00', NULL, NULL),
        (8, 9, 3, '8', 'Test News #8', 'Test News #8', 0, 0, 0, 0, 'hidden', NULL, '2011-08-15 00:00:00', NULL, '2012-08-16 00:00:00')
    ;

    INSERT INTO `download_categories`
        (`id`, `parent_id`, `title`, `slug`, `display_order`, `depth`)
    VALUES
        (1, NULL, 'Test Category #1', '1', 1, 1),
        (2, NULL, 'Test Category #2', '2', 2, 1),
        (3, 1, 'Test Category #3', '3', 2, 1),
        (4, 1, 'Test Category #4', '4', 2, 1),
        (5, 2, 'Test Category #5', '5', 2, 1),
        (6, 2, 'Test Category #6', '6', 2, 1),
        (7, 3, 'Test Category #7', '7', 2, 1),
        (8, 3, 'Test Category #8', '8', 2, 1),
        (9, 7, 'Test Category #9', '9', 2, 1),
        (10, 7, 'Test Category #10', '10', 2, 1),
        (11, 7, 'Test Category #11', '11', 2, 1)
    ;

    INSERT INTO `downloads`
        (`id`, `category_id`, `person_id`, `slug`, `title`, `content`, `view_count`, `total_rating`, `num_comments`,
         `num_ratings`, `status`, `hidden_status`, `date_created`, `date_published`, `date_updated`, `num_downloads`)
    VALUES
        (1, 1, 1, '1', 'Test Download #1', 'Test Download #1', 0, 0, 0, 0, 'published', NULL, '2011-08-02 00:00:00', NULL, '2012-03-03 00:00:00', 0),
        (2, 1, 1, '2', 'Test Download #2', 'Test Download #2', 0, 0, 0, 0, 'published', NULL, '2011-08-03 00:00:00', NULL, NULL, 0),
        (3, 1, 2, '3', 'Test Download #3', 'Test Download #3', 0, 0, 0, 0, 'published', NULL, '2011-08-05 00:00:00', NULL, '2012-08-10 00:00:00', 0),
        (4, 1, 2, '4', 'Test Download #4', 'Test Download #4', 0, 0, 0, 0, 'archived', NULL, '2011-08-04 00:00:00', NULL, '2012-08-06 00:00:00', 0),
        (5, 1, 2, '5', 'Test Download #5', 'Test Download #5', 0, 0, 0, 0, 'hidden', 'draft', '2011-08-11 00:00:00', NULL, NULL, 0),
        (6, 1, 3, '6', 'Test Download #6', 'Test Download #6', 0, 0, 0, 0, 'published', NULL, '2011-08-12 00:00:00', NULL, '2012-08-13 00:00:00', 0),
        (7, 2, 3, '7', 'Test Download #7', 'Test Download #7', 0, 0, 0, 0, 'published', NULL, '2011-08-13 00:00:00', NULL, NULL, 0),
        (8, 9, 3, '8', 'Test Download #8', 'Test Download #8', 0, 0, 0, 0, 'hidden', NULL, '2011-08-15 00:00:00', NULL, '2012-08-16 00:00:00', 0)
    ;

    INSERT INTO `article_comments`
        (`id`, `article_id`, `person_id`, `ip_address`, `email`, `name`, `website`, `content`, `status`, `is_reviewed`, `date_created`)
      VALUES
        (1, 1, 1, '', NULL, NULL, NULL, 'Article comment #1', 'visible', 1, '2011-08-01 00:00:00'),
        (2, 2, 2, '', NULL, NULL, NULL, 'Article comment #2', 'visible', 0, '2011-08-01 00:00:00'),
        (3, 2, 3, '', NULL, NULL, NULL, 'Article comment #3', 'deleted', 0, '2011-08-01 00:00:00')
    ;

    INSERT INTO `news_comments`
        (`id`, `news_id`, `person_id`, `ip_address`, `email`, `name`, `website`, `content`, `status`, `is_reviewed`, `date_created`)
      VALUES
        (1, 1, 1, '', NULL, NULL, NULL, 'News comment #1', 'visible', 1, '2011-08-01 00:00:00'),
        (2, 2, 2, '', NULL, NULL, NULL, 'News comment #2', 'visible', 0, '2011-08-01 00:00:00'),
        (3, 2, 3, '', NULL, NULL, NULL, 'News comment #3', 'deleted', 0, '2011-08-01 00:00:00')
    ;

    INSERT INTO `download_comments`
        (`id`, `download_id`, `person_id`, `ip_address`, `email`, `name`, `website`, `content`, `status`, `is_reviewed`, `date_created`)
      VALUES
        (1, 1, 1, '', NULL, NULL, NULL, 'Download comment #1', 'visible', 1, '2011-08-01 00:00:00'),
        (2, 2, 2, '', NULL, NULL, NULL, 'Download comment #2', 'visible', 0, '2011-08-01 00:00:00'),
        (3, 2, 3, '', NULL, NULL, NULL, 'Download comment #3', 'deleted', 0, '2011-08-01 00:00:00')
    ;
"
);

################################################################################
# TEMPORARY TEST DATA: Glossary
################################################################################

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `glossary_word_definitions`
        (`id`, `definition`)
    VALUES
        (1, 'Definition Text')
    ;


    INSERT INTO `glossary_words`
        (`id`, `definition_id`, `word`)
    VALUES
        (1, 1, 'Word 1'),
        (2, 1, 'Word 2')
    ;
"
);

################################################################################
# TEMPORARY TEST DATA: ArticlePendingCreate
################################################################################

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `article_pending_create`
        (`person_id`, `ticket_id`, `ticket_message_id`, `comment`, `date_created`, `assigned_person_id`)
    VALUES
        (1, NULL, NULL, 'ArticlePendingCreate #1', '2015-09-01 10:05:30', 2),
        (2, NULL, NULL, 'ArticlePendingCreate #2', '2015-09-02 04:12:25', 3)
    ;
"
);

################################################################################
# TEMPORARY TEST DATA: Blobs
################################################################################
$em->getConnection()->executeUpdate(
    "
    INSERT INTO `blobs`
        (`id`, `original_blob_id`, `sys_name`, `storage_loc`, `storage_loc_pref`, `storage_loc_specific`, `save_path`, `file_url`, `filename`, `filesize`, `content_type`, `authcode`, `blob_hash`, `is_media_upload`, `title`, `dim_w`, `dim_h`, `date_created`, `is_temp`)
    VALUES
        (1, NULL, NULL, 'db', NULL, NULL, '1/1WCRRCQJQMCXWXMJ0', NULL, 'app_256.png', 55189, 'image/png', '1WCRRCQJQMCXWXMJ0', '9ace9725e1eddb81702043db2522374c', 0, '', 256, 256, '2015-07-07 11:11:47', 0);
"
);

########################################################
# TEMP DATA
########################################################

$faker = \Faker\Factory::create();

// the content publisher agent guy
$publisher            = new \Application\DeskPRO\Entity\Person();
$publisher->name      = 'Corporate Content';
$publisher->can_agent = true;
$publisher->is_agent  = true;
$publisher->addEmailAddressString('content.publisher@deskprodemo.com');
$publisher->setPassword('publisher');

$em->persist($publisher);
$em->flush($publisher);

// an org
$organization = new \Application\DeskPRO\Entity\Organization();
$organization->setName('Mana Publishing');
$organization->setImportance(5);

// a regular dude
$person       = new \Application\DeskPRO\Entity\Person();
$person->name = 'Joe Kool';
$person->addEmailAddressString('joe@deskprodemo.com');
$person->setPassword('joe');
$person->setOrganization($organization);

// an organization
$mana       = new \Application\DeskPRO\Entity\Person();
$mana->name = 'Mana Ger';
$mana->addEmailAddressString('manager@deskprodemo.com');
$mana->setPassword('manager');
$mana->setOrganization($organization);
$mana->organization_manager = true;
$mana->setOrganizationPosition('MANAGER');

$em->persist($mana);
$em->persist($organization);
$em->flush($mana);
$em->flush($organization);
$em->persist($person);
$em->flush($person);

//////////////////////////////////////////////////////////////
// articles
//////////////////////////////////////////////////////////////

$ac        = new \Application\DeskPRO\Entity\ArticleCategory();
$ac->title = 'Germany Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; ++$i) {
    $a = new \Application\DeskPRO\Entity\Article();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategories(array($ac));
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $em->persist($a);
}

$ac        = new \Application\DeskPRO\Entity\ArticleCategory();
$ac->title = 'Finland Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; ++$i) {
    $a = new \Application\DeskPRO\Entity\Article();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategories(array($ac, $em->getRepository('DeskPRO:ArticleCategory')->find(1)));
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $em->persist($a);
}

$ac        = new \Application\DeskPRO\Entity\ArticleCategory();
$ac->title = 'Japan Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; ++$i) {
    $a = new \Application\DeskPRO\Entity\Article();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategories(array($ac));
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $em->persist($a);
}

//////////////////////////////////////////////////////////////
// news
//////////////////////////////////////////////////////////////

$ac        = new \Application\DeskPRO\Entity\NewsCategory();
$ac->title = 'Canada Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; ++$i) {
    $a = new \Application\DeskPRO\Entity\News();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategory($ac);
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $em->persist($a);
}

$ac        = new \Application\DeskPRO\Entity\NewsCategory();
$ac->title = 'U.S. Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; ++$i) {
    $a = new \Application\DeskPRO\Entity\News();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategory($ac);
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $em->persist($a);
}

$ac = $em->getRepository('DeskPRO:NewsCategory')->find(1);

for ($i = 0; $i < 15; ++$i) {
    $a = new \Application\DeskPRO\Entity\News();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategory($ac);
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $em->persist($a);
}

//////////////////////////////////////////////////////////////
// downloads
//////////////////////////////////////////////////////////////

if (!function_exists('make_blob')) {
    function make_blob(\Symfony\Component\DependencyInjection\ContainerInterface $container = null)
    {
        if (!$container) {
            return;
        }

        $storage = $container->get('blob.storage');
        $blob    = $storage->createBlobRecordFromFile(
            realpath(__DIR__.'/../../../../../web/images/dp-logo-130.png'),
            'dp-logo-130.png',
            'image/png'
        );

        return $blob;
    }
}

$ac        = new \Application\DeskPRO\Entity\DownloadCategory();
$ac->title = 'Canada Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; ++$i) {
    $blob = make_blob($container);
    $a    = new \Application\DeskPRO\Entity\Download();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategory($ac);
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $a->setBlob($blob);
    $em->persist($a);
}

$ac        = new \Application\DeskPRO\Entity\DownloadCategory();
$ac->title = 'U.S. Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; ++$i) {
    $blob = make_blob($container);
    $a    = new \Application\DeskPRO\Entity\Download();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategory($ac);
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $a->setBlob($blob);
    $em->persist($a);
}

$ac = $em->getRepository('DeskPRO:DownloadCategory')->find(1);

for ($i = 0; $i < 15; ++$i) {
    $blob = make_blob($container);
    $a    = new \Application\DeskPRO\Entity\Download();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategory($ac);
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $a->setBlob($blob);
    $em->persist($a);
}

//////////////////////////////////////////////////////////////
// feedback
//////////////////////////////////////////////////////////////

// these are inserted already
$DEFAULT_IDEA_CAT = $em->getRepository('DeskPRO:FeedbackCategory')->find(1);
$FEEDBACK_FEATURE = $em->getRepository('DeskPRO:FeedbackCategory')->find(2);
$FEEDBACK_FEATURE->addUsergroup($USERGROUP_EVERYONE);
$FEEDBACK_BUG = $em->getRepository('DeskPRO:FeedbackCategory')->find(3);
$FEEDBACK_BUG->addUsergroup($USERGROUP_EVERYONE);
$em->flush($FEEDBACK_FEATURE);
$em->flush($FEEDBACK_BUG);

if (!function_exists('rand_fb_status_pair')) {
    function rand_fb_status_pair(\Doctrine\ORM\EntityManager $em)
    {
        $array = array();

        $opts = array(
            \Application\DeskPRO\Entity\Feedback::STATUS_ACTIVE,
            \Application\DeskPRO\Entity\Feedback::STATUS_CLOSED,
        );
        $array['status'] = $opts[rand(0, (count($opts) - 1))];

        $scs = $em->getRepository('DeskPRO:FeedbackStatusCategory')->findBy(
            array(
                'status_type' => $array['status'],
            )
        );

        $array['status_category'] = $scs[rand(0, (count($scs) - 1))];

        return $array;
    }
}

for ($i = 0; $i < 30; ++$i) {
    $a = new \Application\DeskPRO\Entity\Feedback();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(750).'<br><br>'.$faker->text(1000));
    $a->setCategory($DEFAULT_IDEA_CAT);
    $fbinfo = rand_fb_status_pair($em);
    $a->setStatus($fbinfo['status']);
    $a->setStatusCategory($fbinfo['status_category']);
    $a->setPerson($publisher);
    $em->persist($a);
}

for ($i = 0; $i < 30; ++$i) {
    $a = new \Application\DeskPRO\Entity\Feedback();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(750).'<br><br>'.$faker->text(1000));
    $a->setCategory($FEEDBACK_BUG);
    $fbinfo = rand_fb_status_pair($em);
    $a->setStatus($fbinfo['status']);
    $a->setStatusCategory($fbinfo['status_category']);
    $a->setPerson($publisher);
    $em->persist($a);
}

$ac = $em->getRepository('DeskPRO:DownloadCategory')->find(1);

for ($i = 0; $i < 30; ++$i) {
    $a = new \Application\DeskPRO\Entity\Feedback();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(750).'<br><br>'.$faker->text(1000));
    $a->setCategory($FEEDBACK_FEATURE);
    $fbinfo = rand_fb_status_pair($em);
    $a->setStatus($fbinfo['status']);
    $a->setStatusCategory($fbinfo['status_category']);
    $a->setPerson($publisher);
    $em->persist($a);
}

$em->flush();

$em->getConnection()->executeUpdate(
    "
INSERT INTO `products` (`id`, `parent_id`, `title`, `display_order`, `depth`, `root`)
VALUES
	(1, NULL, 'Product 1', 10, 0, NULL),
	(2, NULL, 'Product 2', 20, 0, NULL),
	(3, NULL, 'Product 3', 30, 0, NULL);
INSERT INTO `ticket_priorities` (`id`, `title`, `priority`)
VALUES
	(1, 'Priority 1', 10),
	(2, 'Priority 2', 20),
	(3, 'Priority 3', 30);
INSERT INTO `ticket_categories` (`id`, `parent_id`, `title`, `display_order`)
VALUES
	(1, NULL, 'Category 1', 10),
	(2, NULL, 'Category 2', 20),
	(3, NULL, 'Category 3', 30);
INSERT INTO `ticket_workflows` (`id`, `title`, `display_order`)
VALUES
	(4, 'Workflow 1', 10),
	(5, 'Workflow 2', 20),
	(6, 'Workflow 3', 30);
"
);

$em->getConnection()->executeUpdate(
    "
INSERT INTO `department_permissions` (`department_id`, `usergroup_id`, `person_id`, `app`, `name`, `value`)
VALUES
	(1, 1, NULL, 'tickets', 'full', '1'),
	(2, 1, NULL, 'tickets', 'full', '1'),
	(3, 1, NULL, 'chat', 'full', '1'),
	(4, 1, NULL, 'chat', 'full', '1');

"
);

//
// TEMPORARY CHAT DATA
//

$sql = <<<SQL
INSERT INTO `custom_def_chat` (`id`, `parent_id`, `app_id`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `handler_class`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `default_value`, `is_agent_field`)
VALUES
  (1, NULL, NULL, '', 0, 0, 'Chat text', 'this is a text box for a chat', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Text', X'613A303A7B7D', 1, 1, 0, NULL, 0),
  (2, NULL, NULL, '', 0, 0, 'chatt toggle it\'', 'this is a toggle for chat', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Toggle', X'613A303A7B7D', 1, 1, 0, '', 0);
SQL;

$em->getConnection()->executeUpdate(
    $sql
);

$sql = <<<SQL
INSERT INTO `chat_conversations` (`id`, `department_id`, `agent_id`, `agent_team_id`, `person_id`, `session_id`, `subject`, `status`, `person_name`, `person_email`, `rating_response_time`, `rating_overall`, `rating_comment`, `is_agent`, `is_window`, `date_created`, `date_user_waiting`, `date_assigned`, `date_first_agent_message`, `date_ended`, `should_send_transcript`, `date_transcript_sent`, `total_to_ended`, `ended_by`)
VALUES
	(1, 4, 1, NULL, 8, NULL, 'Hi there, how can I help you? | Hi | Do you have a problem? | File: Screen Shot 2015-11-27 at 11.02.12 AM.png (493.95 KB) | Yes look at this file | ok ill have a look', 'ended', 'Joe', 'joe@deskprodemo.com', NULL, NULL, '', 0, 0, '2015-11-29 20:01:32', NULL, '2015-11-29 20:01:40', '2015-11-29 20:01:47', '2015-11-29 20:22:59', 1, NULL, 1287, 'agent'),
	(2, 3, 1, NULL, 8, NULL, 'hey would ya help me? | I need some help here | Sure, what seems to be the problem? | I cant figure this out at all.... | Well let me help you with that!', 'ended', 'Joe', 'joe@deskprodemo.com', NULL, NULL, '', 0, 0, '2015-11-29 20:03:55', NULL, '2015-11-29 20:04:00', '2015-11-29 20:04:18', '2015-11-29 20:14:53', 1, NULL, 658, 'agent'),
	(3, 3, 1, NULL, 8, NULL, 'oh, well hello there | this is admin can I help you | yes, help me | you should see my custom data', 'ended', 'Joe', 'joe@deskprodemo.com', NULL, NULL, '', 0, 0, '2015-11-29 20:09:51', NULL, '2015-11-29 20:09:57', '2015-11-29 20:10:07', '2015-11-29 20:14:48', 1, NULL, 297, 'agent');

SQL;

$em->getConnection()->executeUpdate(
    $sql
);

$sql = <<<SQL
INSERT INTO `chat_messages` (`id`, `conversation_id`, `author_id`, `tag`, `origin`, `person_name`, `content`, `is_sys`, `is_user_hidden`, `is_html`, `metadata`, `date_created`, `date_received`)
VALUES
	(1, 1, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"message_started\"}', 1, 1, 1, X'613A313A7B733A393A227068726173655F6964223B733A31353A226D6573736167655F73746172746564223B7D', '2015-11-29 20:01:33', NULL),
	(2, 1, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"msg_new_user_track\",\"label\":\" < a href = \\\"http://old-portal.dev:8080/\\\" target = \\\"_blank\\\" title = \\\"http://old-portal.dev:8080/\\\" > old - portal . dev:8080 /< / a>\"}', 1, 1, 1, X'613A333A7B733A31343A226E65775F757365725F747261636B223B733A32373A22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F223B733A353A226C6162656C223B733A3131343A223C6120687265663D22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F22207461726765743D225F626C616E6B22207469746C653D22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F223E6F6C642D706F7274616C2E6465763A383038302F3C2F613E223B733A393A227068726173655F6964223B733A31383A226D73675F6E65775F757365725F747261636B223B7D', '2015-11-29 20:01:33', NULL),
	(3, 1, NULL, 'user_joined.1', '', 'Joe', '{\"phrase_id\":\"message_user - joined\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A353A7B733A31313A22757365725F6A6F696E6564223B623A313B733A31313A22706572736F6E5F6E616D65223B733A31313A2241646D696E2041646D696E223B733A393A22706572736F6E5F6964223B693A313B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31393A226D6573736167655F757365722D6A6F696E6564223B7D', '2015-11-29 20:01:40', '2015-11-29 20:01:45'),
	(4, 1, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"message_assigned\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A383A7B733A31333A22636861745F61737369676E6564223B623A313B733A31313A2261737369676E65645F746F223B693A313B733A31333A2261737369676E65645F6E616D65223B733A31313A2241646D696E2041646D696E223B733A31353A2261737369676E65645F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B733A31353A226F6C645F61737369676E65645F746F223B693A303B733A31373A226F6C645F61737369676E65645F6E616D65223B733A303A22223B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31363A226D6573736167655F61737369676E6564223B7D', '2015-11-29 20:01:40', '2015-11-29 20:01:45'),
	(5, 1, 1, NULL, 'agent', 'Admin Admin', '<div>Hi there, how can I help you?</div>', 0, 0, 1, X'613A323A7B733A31333A22706572736F6E5F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D343026643D6D6D223B733A31383A22706572736F6E5F6176617461725F69636F6E223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B7D', '2015-11-29 20:01:47', '2015-11-29 20:01:51'),
	(6, 1, NULL, NULL, 'user', 'Joe', 'Hi', 0, 0, 0, X'613A313A7B733A31353A2269735F757365725F6D657373616765223B623A313B7D', '2015-11-29 20:01:54', NULL),
	(7, 1, 1, NULL, 'agent', 'Admin Admin', 'Do you have a problem?', 0, 0, 1, X'613A323A7B733A31333A22706572736F6E5F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D343026643D6D6D223B733A31383A22706572736F6E5F6176617461725F69636F6E223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B7D', '2015-11-29 20:02:18', '2015-11-29 20:02:21'),
	(11, 1, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"msg_new_user_track\",\"label\":\" < a href = \\\"http://old-portal.dev:8080/\\\" target = \\\"_blank\\\" title = \\\"http://old-portal.dev:8080/\\\" > old - portal . dev:8080 /< / a>\"}', 1, 1, 1, X'613A333A7B733A31343A226E65775F757365725F747261636B223B733A32373A22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F223B733A353A226C6162656C223B733A3131343A223C6120687265663D22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F22207461726765743D225F626C616E6B22207469746C653D22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F223E6F6C642D706F7274616C2E6465763A383038302F3C2F613E223B733A393A227068726173655F6964223B733A31383A226D73675F6E65775F757365725F747261636B223B7D', '2015-11-29 20:02:50', NULL),
	(12, 2, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"message_started\"}', 1, 1, 1, X'613A313A7B733A393A227068726173655F6964223B733A31353A226D6573736167655F73746172746564223B7D', '2015-11-29 20:03:55', NULL),
	(13, 2, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"msg_new_user_track\",\"label\":\" < a href = \\\"http://old-portal.dev:8080/\\\" target = \\\"_blank\\\" title = \\\"http://old-portal.dev:8080/\\\" > old - portal . dev:8080 /< / a>\"}', 1, 1, 1, X'613A333A7B733A31343A226E65775F757365725F747261636B223B733A32373A22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F223B733A353A226C6162656C223B733A3131343A223C6120687265663D22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F22207461726765743D225F626C616E6B22207469746C653D22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F223E6F6C642D706F7274616C2E6465763A383038302F3C2F613E223B733A393A227068726173655F6964223B733A31383A226D73675F6E65775F757365725F747261636B223B7D', '2015-11-29 20:03:55', NULL),
	(14, 2, NULL, 'user_joined.1', '', 'Joe', '{\"phrase_id\":\"message_user - joined\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A353A7B733A31313A22757365725F6A6F696E6564223B623A313B733A31313A22706572736F6E5F6E616D65223B733A31313A2241646D696E2041646D696E223B733A393A22706572736F6E5F6964223B693A313B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31393A226D6573736167655F757365722D6A6F696E6564223B7D', '2015-11-29 20:04:00', '2015-11-29 20:04:04'),
	(15, 2, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"message_assigned\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A383A7B733A31333A22636861745F61737369676E6564223B623A313B733A31313A2261737369676E65645F746F223B693A313B733A31333A2261737369676E65645F6E616D65223B733A31313A2241646D696E2041646D696E223B733A31353A2261737369676E65645F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B733A31353A226F6C645F61737369676E65645F746F223B693A303B733A31373A226F6C645F61737369676E65645F6E616D65223B733A303A22223B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31363A226D6573736167655F61737369676E6564223B7D', '2015-11-29 20:04:00', '2015-11-29 20:04:04'),
	(16, 2, NULL, NULL, 'user', 'Joe', 'hey would ya help me?', 0, 0, 0, X'613A313A7B733A31353A2269735F757365725F6D657373616765223B623A313B7D', '2015-11-29 20:04:07', NULL),
	(17, 2, NULL, NULL, 'user', 'Joe', 'I need some help here', 0, 0, 0, X'613A313A7B733A31353A2269735F757365725F6D657373616765223B623A313B7D', '2015-11-29 20:04:10', NULL),
	(18, 2, 1, NULL, 'agent', 'Joe', '<div>Sure, what seems to be the problem?</div>', 0, 0, 1, X'613A323A7B733A31333A22706572736F6E5F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D343026643D6D6D223B733A31383A22706572736F6E5F6176617461725F69636F6E223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B7D', '2015-11-29 20:04:18', '2015-11-29 20:04:23'),
	(19, 2, NULL, NULL, 'user', 'Joe', 'I cant figure this out at all....', 0, 0, 0, X'613A313A7B733A31353A2269735F757365725F6D657373616765223B623A313B7D', '2015-11-29 20:04:26', NULL),
	(20, 2, 1, NULL, 'agent', 'Admin Admin', 'Well let me help you with that!', 0, 0, 1, X'613A323A7B733A31333A22706572736F6E5F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D343026643D6D6D223B733A31383A22706572736F6E5F6176617461725F69636F6E223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B7D', '2015-11-29 20:04:36', '2015-11-29 20:04:40'),
	(21, 3, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"message_started\"}', 1, 1, 1, X'613A313A7B733A393A227068726173655F6964223B733A31353A226D6573736167655F73746172746564223B7D', '2015-11-29 20:09:51', NULL),
	(22, 3, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"msg_new_user_track\",\"label\":\" < a href = \\\"http://old-portal.dev:8080/\\\" target = \\\"_blank\\\" title = \\\"http://old-portal.dev:8080/\\\" > old - portal . dev:8080 /< / a>\"}', 1, 1, 1, X'613A333A7B733A31343A226E65775F757365725F747261636B223B733A32373A22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F223B733A353A226C6162656C223B733A3131343A223C6120687265663D22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F22207461726765743D225F626C616E6B22207469746C653D22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F223E6F6C642D706F7274616C2E6465763A383038302F3C2F613E223B733A393A227068726173655F6964223B733A31383A226D73675F6E65775F757365725F747261636B223B7D', '2015-11-29 20:09:51', NULL),
	(23, 3, NULL, 'user_joined.1', '', 'Joe', '{\"phrase_id\":\"message_user - joined\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A353A7B733A31313A22757365725F6A6F696E6564223B623A313B733A31313A22706572736F6E5F6E616D65223B733A31313A2241646D696E2041646D696E223B733A393A22706572736F6E5F6964223B693A313B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31393A226D6573736167655F757365722D6A6F696E6564223B7D', '2015-11-29 20:09:57', '2015-11-29 20:10:00'),
	(24, 3, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"message_assigned\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A383A7B733A31333A22636861745F61737369676E6564223B623A313B733A31313A2261737369676E65645F746F223B693A313B733A31333A2261737369676E65645F6E616D65223B733A31313A2241646D696E2041646D696E223B733A31353A2261737369676E65645F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B733A31353A226F6C645F61737369676E65645F746F223B693A303B733A31373A226F6C645F61737369676E65645F6E616D65223B733A303A22223B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31363A226D6573736167655F61737369676E6564223B7D', '2015-11-29 20:09:57', '2015-11-29 20:10:00'),
	(25, 3, 1, NULL, 'agent', 'Admin Admin', '<div>oh, well hello there</div>', 0, 0, 1, X'613A323A7B733A31333A22706572736F6E5F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D343026643D6D6D223B733A31383A22706572736F6E5F6176617461725F69636F6E223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B7D', '2015-11-29 20:10:07', '2015-11-29 20:10:12'),
	(26, 3, 1, NULL, 'agent', 'Admin Admin', 'this is admin can I help you', 0, 0, 1, X'613A323A7B733A31333A22706572736F6E5F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D343026643D6D6D223B733A31383A22706572736F6E5F6176617461725F69636F6E223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B7D', '2015-11-29 20:10:28', '2015-11-29 20:10:30'),
	(27, 3, NULL, NULL, 'user', 'Joe', 'yes, help me', 0, 0, 0, X'613A313A7B733A31353A2269735F757365725F6D657373616765223B623A313B7D', '2015-11-29 20:10:33', NULL),
	(28, 3, NULL, NULL, 'user', 'Joe', 'you should see my custom data', 0, 0, 0, X'613A313A7B733A31353A2269735F757365725F6D657373616765223B623A313B7D', '2015-11-29 20:10:39', NULL),
	(29, 1, NULL, 'user_joined.1', '', 'Joe', '{\"phrase_id\":\"message_user - joined\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A353A7B733A31313A22757365725F6A6F696E6564223B623A313B733A31313A22706572736F6E5F6E616D65223B733A31313A2241646D696E2041646D696E223B733A393A22706572736F6E5F6964223B693A313B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31393A226D6573736167655F757365722D6A6F696E6564223B7D', '2015-11-29 20:11:04', NULL),
	(30, 2, NULL, 'user_joined.1', '', 'Joe', '{\"phrase_id\":\"message_user - joined\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A353A7B733A31313A22757365725F6A6F696E6564223B623A313B733A31313A22706572736F6E5F6E616D65223B733A31313A2241646D696E2041646D696E223B733A393A22706572736F6E5F6964223B693A313B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31393A226D6573736167655F757365722D6A6F696E6564223B7D', '2015-11-29 20:11:04', NULL),
	(31, 3, NULL, 'user_joined.1', '', 'Joe', '{\"phrase_id\":\"message_user - joined\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A353A7B733A31313A22757365725F6A6F696E6564223B623A313B733A31313A22706572736F6E5F6E616D65223B733A31313A2241646D696E2041646D696E223B733A393A22706572736F6E5F6964223B693A313B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31393A226D6573736167655F757365722D6A6F696E6564223B7D', '2015-11-29 20:11:05', '2015-11-29 20:11:10'),
	(32, 3, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"message_ended - by\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A333A7B733A31303A22636861745F656E646564223B623A313B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31363A226D6573736167655F656E6465642D6279223B7D', '2015-11-29 20:14:48', NULL),
	(33, 2, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"message_ended - by\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A333A7B733A31303A22636861745F656E646564223B623A313B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31363A226D6573736167655F656E6465642D6279223B7D', '2015-11-29 20:14:53', NULL);

SQL;

$em->getConnection()->executeUpdate(
    $sql
);

$sql = <<<SQL
  INSERT INTO `custom_data_chat` (`id`, `conversation_id`, `field_id`, `root_field_id`, `value`, `input`)
VALUES
  (1, 3, 1, 1, 0, 'this is a custom chat text answer!'),
  (2, 3, 2, 2, 1, '');
SQL;

$em->getConnection()->executeUpdate(
    $sql
);

################################################################################
# Agent Alerts
################################################################################

$sql = <<<SQL
  INSERT INTO `agent_alerts` (`id`, `person_id`, `typename`, `data`, `date_created`, `is_dismissed`)
            VALUES
                (1, 1, 'tickets', 0x613a31303a7b733a31323a224066657463685f7479706573223b613a333a7b733a363a227469636b6574223b733a31343a224465736b50524f3a5469636b6574223b733a393a22706572666f726d6572223b733a31343a224465736b50524f3a506572736f6e223b733a393a226c6f675f6974656d73223b733a31373a224465736b50524f3a5469636b65744c6f67223b7d733a363a227469636b6574223b693a3536373b733a393a22706572666f726d6572223b693a3532383b733a31333a2269735f6e65775f7469636b6574223b623a313b733a31383a2269735f6e65775f6167656e745f7265706c79223b623a303b733a31373a2269735f6e65775f6167656e745f6e6f7465223b623a303b733a31373a2269735f6e65775f757365725f7265706c79223b623a313b733a393a226c6f675f6974656d73223b613a383a7b693a303b693a313b693a313b693a323b693a323b693a333b693a333b693a343b693a343b693a353b693a353b693a363b693a363b693a373b693a373b693a383b7d733a31363a2262726f777365725f72656e6465726564223b733a3438393a223c6c690a09636c6173733d22696e73696465207469636b6574206e65772d7469636b6574207469636b65742d726f772d353637207469636b65742d353637220a09646174612d636c6173732d69643d227469636b65742d726f772d353637220a09646174612d747970653d227469636b657473220a09646174612d726f7574653d227469636b65743a2f696e6465782e7068702f6f6c642d6167656e742f7469636b6574732f353637220a09646174612d726f7574652d6e6f74616272656c6f61643d2231220a3e0a093c64697620636c6173733d226469736d697373223e3c6920636c6173733d2269636f6e2d62616e2d636972636c65223e3c2f693e3c2f6469763e0a093c74696d65206461746574696d653d22323031362d30312d32305430333a30363a32382b30303a3030223e3c2f74696d653e0a093c6269673e0a09093c7370616e20636c6173733d22726f772d6964223e233536373c2f7370616e3e0a090954657374204d657373616765202330333036202d2d20323031362d30312d3230202d2d2032380a093c2f6269673e0a093c736d616c6c3e0a0909090909202020202020202020202020094e6577207469636b6574206279205573657220287573657240666f6f6261722e636f6d290a0909090909093c2f736d616c6c3e0a3c2f6c693e0a223b733a31323a22407461726765745f6d617073223b613a313a7b733a373a2262726f77736572223b613a313a7b693a303b733a31363a2262726f777365725f72656e6465726564223b7d7d7d, '2016-01-20 03:06:28', 0),
                (2, 1, 'tickets', 0x613a31303a7b733a31323a224066657463685f7479706573223b613a333a7b733a363a227469636b6574223b733a31343a224465736b50524f3a5469636b6574223b733a393a22706572666f726d6572223b733a31343a224465736b50524f3a506572736f6e223b733a393a226c6f675f6974656d73223b733a31373a224465736b50524f3a5469636b65744c6f67223b7d733a363a227469636b6574223b693a3536383b733a393a22706572666f726d6572223b693a3532393b733a31333a2269735f6e65775f7469636b6574223b623a313b733a31383a2269735f6e65775f6167656e745f7265706c79223b623a303b733a31373a2269735f6e65775f6167656e745f6e6f7465223b623a303b733a31373a2269735f6e65775f757365725f7265706c79223b623a313b733a393a226c6f675f6974656d73223b613a383a7b693a303b693a393b693a313b693a31303b693a323b693a31313b693a333b693a31323b693a343b693a31333b693a353b693a31343b693a363b693a31353b693a373b693a31363b7d733a31363a2262726f777365725f72656e6465726564223b733a3439313a223c6c690a09636c6173733d22696e73696465207469636b6574206e65772d7469636b6574207469636b65742d726f772d353638207469636b65742d353638220a09646174612d636c6173732d69643d227469636b65742d726f772d353638220a09646174612d747970653d227469636b657473220a09646174612d726f7574653d227469636b65743a2f696e6465782e7068702f6f6c642d6167656e742f7469636b6574732f353638220a09646174612d726f7574652d6e6f74616272656c6f61643d2231220a3e0a093c64697620636c6173733d226469736d697373223e3c6920636c6173733d2269636f6e2d62616e2d636972636c65223e3c2f693e3c2f6469763e0a093c74696d65206461746574696d653d22323031362d30312d32305430353a30393a35302b30303a3030223e3c2f74696d653e0a093c6269673e0a09093c7370616e20636c6173733d22726f772d6964223e233536383c2f7370616e3e0a090954657374204d657373616765202330353039202d2d20323031362d30312d3230202d2d2035300a093c2f6269673e0a093c736d616c6c3e0a0909090909202020202020202020202020094e6577207469636b65742062792055736572312028757365723140666f6f6261722e636f6d290a0909090909093c2f736d616c6c3e0a3c2f6c693e0a223b733a31323a22407461726765745f6d617073223b613a313a7b733a373a2262726f77736572223b613a313a7b693a303b733a31363a2262726f777365725f72656e6465726564223b7d7d7d, '2016-01-20 05:09:50', 0),
                (3, 1, 'tickets', 0x613a31303a7b733a31323a224066657463685f7479706573223b613a333a7b733a363a227469636b6574223b733a31343a224465736b50524f3a5469636b6574223b733a393a22706572666f726d6572223b733a31343a224465736b50524f3a506572736f6e223b733a393a226c6f675f6974656d73223b733a31373a224465736b50524f3a5469636b65744c6f67223b7d733a363a227469636b6574223b693a3536393b733a393a22706572666f726d6572223b693a3533303b733a31333a2269735f6e65775f7469636b6574223b623a313b733a31383a2269735f6e65775f6167656e745f7265706c79223b623a303b733a31373a2269735f6e65775f6167656e745f6e6f7465223b623a303b733a31373a2269735f6e65775f757365725f7265706c79223b623a313b733a393a226c6f675f6974656d73223b613a383a7b693a303b693a31373b693a313b693a31383b693a323b693a31393b693a333b693a32303b693a343b693a32313b693a353b693a32323b693a363b693a32333b693a373b693a32343b7d733a31363a2262726f777365725f72656e6465726564223b733a3439313a223c6c690a09636c6173733d22696e73696465207469636b6574206e65772d7469636b6574207469636b65742d726f772d353639207469636b65742d353639220a09646174612d636c6173732d69643d227469636b65742d726f772d353639220a09646174612d747970653d227469636b657473220a09646174612d726f7574653d227469636b65743a2f696e6465782e7068702f6f6c642d6167656e742f7469636b6574732f353639220a09646174612d726f7574652d6e6f74616272656c6f61643d2231220a3e0a093c64697620636c6173733d226469736d697373223e3c6920636c6173733d2269636f6e2d62616e2d636972636c65223e3c2f693e3c2f6469763e0a093c74696d65206461746574696d653d22323031362d30312d32305430353a31303a30302b30303a3030223e3c2f74696d653e0a093c6269673e0a09093c7370616e20636c6173733d22726f772d6964223e233536393c2f7370616e3e0a090954657374204d657373616765202330353130202d2d20323031362d30312d3230202d2d2030300a093c2f6269673e0a093c736d616c6c3e0a0909090909202020202020202020202020094e6577207469636b65742062792055736572322028757365723240666f6f6261722e636f6d290a0909090909093c2f736d616c6c3e0a3c2f6c693e0a223b733a31323a22407461726765745f6d617073223b613a313a7b733a373a2262726f77736572223b613a313a7b693a303b733a31363a2262726f777365725f72656e6465726564223b7d7d7d, '2016-01-20 05:10:00', 0),
                (4, 1, 'tickets', 0x613a31303a7b733a31323a224066657463685f7479706573223b613a333a7b733a363a227469636b6574223b733a31343a224465736b50524f3a5469636b6574223b733a393a22706572666f726d6572223b733a31343a224465736b50524f3a506572736f6e223b733a393a226c6f675f6974656d73223b733a31373a224465736b50524f3a5469636b65744c6f67223b7d733a363a227469636b6574223b693a3536393b733a393a22706572666f726d6572223b693a3533303b733a31333a2269735f6e65775f7469636b6574223b623a303b733a31383a2269735f6e65775f6167656e745f7265706c79223b623a303b733a31373a2269735f6e65775f6167656e745f6e6f7465223b623a303b733a31373a2269735f6e65775f757365725f7265706c79223b623a313b733a393a226c6f675f6974656d73223b613a333a7b693a303b693a32353b693a313b693a32363b693a323b693a32373b7d733a31363a2262726f777365725f72656e6465726564223b733a3438373a223c6c690a09636c6173733d22696e73696465207469636b6574206e65772d7265706c79207469636b65742d726f772d353639207469636b65742d353639220a09646174612d636c6173732d69643d227469636b65742d726f772d353639220a09646174612d747970653d227469636b657473220a09646174612d726f7574653d227469636b65743a2f696e6465782e7068702f6f6c642d6167656e742f7469636b6574732f353639220a09646174612d726f7574652d6e6f74616272656c6f61643d2231220a3e0a093c64697620636c6173733d226469736d697373223e3c6920636c6173733d2269636f6e2d62616e2d636972636c65223e3c2f693e3c2f6469763e0a093c74696d65206461746574696d653d22323031362d30312d32305430353a31303a35362b30303a3030223e3c2f74696d653e0a093c6269673e0a09093c7370616e20636c6173733d22726f772d6964223e233536393c2f7370616e3e0a090954657374204d657373616765202330353130202d2d20323031362d30312d3230202d2d2030300a093c2f6269673e0a093c736d616c6c3e0a09092020202020202020202020204e65772075736572207265706c792062792055736572322028757365723240666f6f6261722e636f6d290a0909093c2f736d616c6c3e0a3c2f6c693e0a223b733a31323a22407461726765745f6d617073223b613a313a7b733a373a2262726f77736572223b613a313a7b693a303b733a31363a2262726f777365725f72656e6465726564223b7d7d7d, '2016-01-20 05:10:56', 0);
SQL;

$em->getConnection()->executeUpdate(
    $sql
);
