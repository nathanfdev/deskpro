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
 * @category Entities
 */

namespace Application\DeskPRO\Languages;

use Application\DeskPRO\App;
use Application\DeskPRO\CustomFields\FieldManager;
use Application\DeskPRO\Departments\ChatDepartments;
use Application\DeskPRO\Departments\TicketDepartments;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\EntityRepository\ArticleCategory as ArticleCategoryRepository;
use Application\DeskPRO\EntityRepository\Phrase as PhraseRepository;
use Application\DeskPRO\Products\Products;
use Application\DeskPRO\Tickets\TicketCategories;
use Application\DeskPRO\Tickets\TicketPriorities;
use Application\DeskPRO\Tickets\TicketWorkflows;

class PhraseData
{
    /**
     * @var \Application\DeskPRO\EntityRepository\Phrase
     */
    private $phrase_repos;

    /**
     * @var string
     */
    private $lang_dir;

    public function __construct(PhraseRepository $phrase_repos, $lang_dir)
    {
        $this->phrase_repos = $phrase_repos;
        $this->lang_dir     = rtrim($lang_dir, '/');
    }


    /**
     * @param  TicketDepartments $ticket_deps
     * @param  Language          $language
     * @return array
     */
    public function getTicketDepartmentPhrases(TicketDepartments $ticket_deps, Language $language = null)
    {
        $phrase_group = 'obj_department';

        if ($language) {
            $custom_phrases = $this->loadCustomPhrases($language, $phrase_group);
        } else {
            $custom_phrases = array();
        }

        $phrase_data = array();
        $flat_array = $ticket_deps->getFlatArray();

        foreach (array('agent', 'user') as $type) {
            foreach ($flat_array as $dep_row) {
                /** @var \Application\DeskPRO\Entity\Department $dep */
                $dep = $dep_row['object'];

                if ($type == 'user') {
                    $id = $phrase_group . '.' . $dep->id . '_user';
                    $id2 = $phrase_group . '.' . $dep->id . '_title';
                    $custom = isset($custom_phrases[$id]) ? $custom_phrases[$id] : null;
                    if (!$custom) {
                        $custom = isset($custom_phrases[$id2]) ? $custom_phrases[$id2] : null;
                    }
                } else {
                    $id = $phrase_group . '.' . $dep->id . '_title';
                    $custom = isset($custom_phrases[$id]) ? $custom_phrases[$id] : null;
                }

                $row = array(
                    'id'      => $id,
                    'depth'   => $dep_row['depth'],
                    'type'    => 'ticket_department',
                    'type_id' => $dep->id,
                    'default' => $dep->title,
                    'lang'    => $dep->user_title ? : $dep->title,
                    'custom'  => $custom,
                    'type'    => $type,
                );

                // The default for the language
                $row['lang_default'] = $row['lang'] ? : $row['default'];

                // The actual set value that will be used
                $row['set'] = $row['custom'] ? : $row['lang_default'];

                $phrase_data[] = $row;
            }
        }

        return $phrase_data;
    }


    /**
     * @param  TicketCategories $ticket_cats
     * @param  Language         $language
     * @return array
     */
    public function getTicketCategoryPhrases(TicketCategories $ticket_cats, Language $language = null)
    {
        $phrase_group = 'obj_ticketcategory';

        if ($language) {
            $custom_phrases = $this->loadCustomPhrases($language, $phrase_group);
        } else {
            $custom_phrases = array();
        }

        $phrase_data = array();

        foreach ($ticket_cats->getFlatArray() as $cat_row) {
            /** @var \Application\DeskPRO\Entity\TicketCategory $cat */
            $cat = $cat_row['object'];

            $id = $phrase_group . '.' . $cat->id . '_title';

            $row = array(
                'id'      => $id,
                'depth'   => $cat_row['depth'],
                'type'    => 'ticket_category',
                'type_id' => $cat->id,
                'default' => $cat->title,
                'lang'    => $cat->title,
                'custom'  => isset($custom_phrases[$id]) ? $custom_phrases[$id] : null,
            );

            // The default for the language
            $row['lang_default'] = $row['lang'] ?: $row['default'];

            // The actual set value that will be used
            $row['set'] = $row['custom'] ?: $row['lang_default'];

            $phrase_data[] = $row;
        }

        return $phrase_data;
    }


    /**
     * @param  TicketWorkflows $ticket_works
     * @param  Language        $language
     * @return array
     */
    public function getTicketWorkflowPhrases(TicketWorkflows $ticket_works, Language $language = null)
    {
        $phrase_group = 'obj_ticketworkflow';

        if ($language) {
            $custom_phrases = $this->loadCustomPhrases($language, $phrase_group);
        } else {
            $custom_phrases = array();
        }

        $phrase_data = array();

        foreach ($ticket_works->getAll() as $work) {
            $id = $phrase_group . '.' . $work->id . '_title';

            $row = array(
                'id'      => $id,
                'depth'   => 0,
                'type'    => 'ticket_category',
                'type_id' => $work->id,
                'default' => $work->title,
                'lang'    => $work->title,
                'custom'  => isset($custom_phrases[$id]) ? $custom_phrases[$id] : null,
            );

            // The default for the language
            $row['lang_default'] = $row['lang'] ?: $row['default'];

            // The actual set value that will be used
            $row['set'] = $row['custom'] ?: $row['lang_default'];

            $phrase_data[] = $row;
        }

        return $phrase_data;
    }


    /**
     * @param  TicketPriorities $ticket_pris
     * @param  Language         $language
     * @return array
     */
    public function getTicketPriorityPhrases(TicketPriorities $ticket_pris, Language $language = null)
    {
        $phrase_group = 'obj_ticketpriority';

        if ($language) {
            $custom_phrases = $this->loadCustomPhrases($language, $phrase_group);
        } else {
            $custom_phrases = array();
        }

        $phrase_data = array();

        foreach ($ticket_pris->getAll() as $pri) {
            $id = $phrase_group . '.' . $pri->id . '_title';

            $row = array(
                'id'      => $id,
                'depth'   => 0,
                'type'    => 'ticket_category',
                'type_id' => $pri->id,
                'default' => $pri->title,
                'lang'    => $pri->title,
                'custom'  => isset($custom_phrases[$id]) ? $custom_phrases[$id] : null,
            );

            // The default for the language
            $row['lang_default'] = $row['lang'] ?: $row['default'];

            // The actual set value that will be used
            $row['set'] = $row['custom'] ?: $row['lang_default'];

            $phrase_data[] = $row;
        }

        return $phrase_data;
    }


    /**
     * @param  ChatDepartments $chat_deps
     * @param  Language        $language
     * @return array
     */
    public function getChatDepartmentPhrases(ChatDepartments $chat_deps, Language $language = null)
    {
        $phrase_group = 'obj_department';

        if ($language) {
            $custom_phrases = $this->loadCustomPhrases($language, $phrase_group);
        } else {
            $custom_phrases = array();
        }

        $phrase_data = array();

        foreach ($chat_deps->getFlatArray() as $dep_row) {
            /** @var \Application\DeskPRO\Entity\Department $dep */
            $dep   = $dep_row['object'];

            $id = $phrase_group . '.' . $dep->id . '_title';

            $row = array(
                'id'      => $id,
                'depth'   => $dep_row['depth'],
                'type'    => 'ticket_department',
                'type_id' => $dep->id,
                'default' => $dep->title,
                'lang'    => $dep->user_title ?: $dep->title,
                'custom'  => isset($custom_phrases[$id]) ? $custom_phrases[$id] : null,
            );

            // The default for the language
            $row['lang_default'] = $row['lang'] ?: $row['default'];

            // The actual set value that will be used
            $row['set'] = $row['custom'] ?: $row['lang_default'];

            $phrase_data[] = $row;
        }

        return $phrase_data;
    }


    /**
     * @param  Products $products
     * @param  Language $language
     * @return array
     */
    public function getProductPhrases(Products $products, Language $language = null)
    {
        $phrase_group = 'obj_product';

        if ($language) {
            $custom_phrases = $this->loadCustomPhrases($language, $phrase_group);
        } else {
            $custom_phrases = array();
        }

        $phrase_data = array();

        foreach ($products->getFlatArray() as $prod_row) {
            /** @var \Application\DeskPRO\Entity\Product $prod */
            $prod   = $prod_row['object'];

            $id = $phrase_group . '.' . $prod->id . '_title';

            $row = array(
                'id'      => $id,
                'depth'   => $prod_row['depth'],
                'type'    => 'product',
                'type_id' => $prod->id,
                'default' => $prod->title,
                'lang'    => $prod->title,
                'custom'  => isset($custom_phrases[$id]) ? $custom_phrases[$id] : null,
            );

            // The default for the language
            $row['lang_default'] = $row['lang'] ?: $row['default'];

            // The actual set value that will be used
            $row['set'] = $row['custom'] ?: $row['lang_default'];

            $phrase_data[] = $row;
        }

        return $phrase_data;
    }


    /**
     * @param  FieldManager $fm
     * @param  Language     $language
     * @return array
     */
    public function getFieldPhrases(FieldManager $fm, Language $language = null)
    {
        switch ($fm->getEntityName()) {
            case 'DeskPRO:CustomDefTicket':
                $phrase_group = 'obj_customdefticket';
                break;

            case 'DeskPRO:CustomDefPerson':
                $phrase_group = 'obj_customdefperson';
                break;

            case 'DeskPRO:CustomDefOrganization':
                $phrase_group = 'obj_customdeforganization';
                break;

            default: return array();
        }

        if ($language) {
            $custom_phrases = $this->loadCustomPhrases($language, $phrase_group);
        } else {
            $custom_phrases = array();
        }

        $fn_get_rows = function (CustomDefAbstract $field, $depth = 0) use ($phrase_group, $custom_phrases, $fm, &$fn_get_rows) {
            $id = $phrase_group . '.' . $field->id . '_title';

            $row = array(
                'id'      => $id,
                'depth'   => $depth,
                'type'    => 'field',
                'type_id' => $field->id,
                'default' => $field->title,
                'lang'    => $field->title,
                'custom'  => isset($custom_phrases[$id]) ? $custom_phrases[$id] : null,
            );

            $row['lang_default'] = $row['lang'] ?: $row['default'];
            $row['set'] = $row['custom'] ?: $row['lang_default'];

            $phrase_data = array($row);

            if ($children = $fm->getFieldChildren($field)) {
                foreach ($children as $child_field) {
                    array_merge($phrase_data, $fn_get_rows($child_field, $depth+1));
                }
            }

            return $phrase_data;
        };

        $phrase_data = array();

        foreach ($fm->getFields() as $field) {
            $phrase_data = array_merge($phrase_data, $fn_get_rows($field, 0));
        }

        return $phrase_data;
    }


    /**
     * @param  Language $language
     * @return array
     */
    public function getFeedbackStatusPhrases(Language $language = null)
    {
        //TODO: need a feedback service
        $em = App::getOrm();

        $phrase_group = 'obj_feedbackstatuscategory';

        $all_statuses = $em->createQuery("
            SELECT s
            FROM DeskPRO:FeedbackStatusCategory s
            ORDER BY s.display_order ASC
        ")->getResult();

        if ($language) {
            $custom_phrases = $this->loadCustomPhrases($language, $phrase_group);
        } else {
            $custom_phrases = array();
        }

        $phrase_data = array();

        foreach ($all_statuses as $status) {
            $id = $phrase_group . '.' . $status->id . '_title';

            $row = array(
                'id'      => $id,
                'depth'   => 0,
                'type'    => 'feedback_status',
                'type_id' => $status->id,
                'default' => $status->title,
                'lang'    => $status->title,
                'custom'  => isset($custom_phrases[$id]) ? $custom_phrases[$id] : null,
            );

            $row['lang_default'] = $row['lang'] ?: $row['default'];
            $row['set'] = $row['custom'] ?: $row['lang_default'];

            $phrase_data[] = $row;
        }

        return $phrase_data;
    }

    /**
     * @param  Language $language
     * @return array
     */
    public function getFeedbackTypePhrases(Language $language = null)
    {
        //TODO: need a feedback service
        $em = App::getOrm();

        $phrase_group = 'obj_feedbackcategory';

        $all_types = $em->createQuery("
            SELECT s
            FROM DeskPRO:FeedbackCategory s
            ORDER BY s.display_order ASC
        ")->getResult();

        if ($language) {
            $custom_phrases = $this->loadCustomPhrases($language, $phrase_group);
        } else {
            $custom_phrases = array();
        }

        $phrase_data = array();

        foreach ($all_types as $type) {
            $id = $phrase_group . '.' . $type->id . '_title';

            $row = array(
                'id'      => $id,
                'depth'   => 0,
                'type'    => 'feedback_status',
                'type_id' => $type->id,
                'default' => $type->title,
                'lang'    => $type->title,
                'custom'  => isset($custom_phrases[$id]) ? $custom_phrases[$id] : null,
            );

            $row['lang_default'] = $row['lang'] ?: $row['default'];
            $row['set'] = $row['custom'] ?: $row['lang_default'];

            $phrase_data[] = $row;
        }

        return $phrase_data;
    }


    /**
     * @param  ArticleCategoryRepository $repos
     * @param  Language                  $language
     * @return array
     */
    public function getKbCategoryPhrases(ArticleCategoryRepository $repos, Language $language = null)
    {
        $phrase_group = 'obj_articlecategory';

        if ($language) {
            $custom_phrases = $this->loadCustomPhrases($language, $phrase_group);
        } else {
            $custom_phrases = array();
        }

        $all = $repos->getAllIndexedById();
        $flat = $repos->getFlatHierarchy();

        $phrase_data = array();

        foreach ($flat as $cat_row) {
            /** @var \Application\DeskPRO\Entity\ArticleCategory $cat */
            $cat = $all[$cat_row['id']];

            $id = $phrase_group . '.' . $cat->id . '_title';

            $row = array(
                'id'      => $id,
                'depth'   => $cat_row['depth'],
                'type'    => 'kb_category',
                'type_id' => $cat->id,
                'default' => $cat->title,
                'lang'    => $cat->title,
                'custom'  => isset($custom_phrases[$id]) ? $custom_phrases[$id] : null,
            );

            // The default for the language
            $row['lang_default'] = $row['lang'] ?: $row['default'];

            // The actual set value that will be used
            $row['set'] = $row['custom'] ?: $row['lang_default'];

            $phrase_data[] = $row;
        }

        return $phrase_data;
    }


    /**
     * @param  Language|null $language
     * @param  string        $group_id
     * @return array
     */
    public function loadGroup(Language $language = null, $group_id)
    {
        $default_phrases = $this->loadSystemPhrases('default', $group_id);

        if ($language) {
            $lang_phrases    = $this->loadSystemPhrases($language->sys_name, $group_id);
            $custom_phrases  = $this->loadCustomPhrases($language, $group_id);
        } else {
            $lang_phrases = array();
            $custom_phrases = array();
        }

        $phrase_ids = array_merge(
            array_keys($default_phrases),
            array_keys($lang_phrases),
            array_keys($custom_phrases)
        );
        $phrase_ids = array_unique($phrase_ids);
        sort($phrase_ids, \SORT_STRING);

        $phrase_data = array();
        foreach ($phrase_ids as $id) {
            $row = array(
                'id'      => $id,
                'default' => isset($default_phrases[$id]) ? $default_phrases[$id] : null,
                'lang'    => isset($lang_phrases[$id]) ? $lang_phrases[$id] : null,
                'custom'  => isset($custom_phrases[$id]) ? $custom_phrases[$id] : null,
            );

            // The default for the language
            $row['lang_default'] = $row['lang'] ?: $row['default'];

            // The actual set value that will be used
            $row['set'] = $row['custom'] ?: $row['lang_default'];

            $phrase_data[] = $row;
        }

        return $phrase_data;
    }


    /**
     * @param  Language $language
     * @return array
     */
    public function loadCustom(Language $language)
    {
        $custom_phrases = $this->phrase_repos->getCustomPhrases($language);
        $custom_phrases = array_filter($custom_phrases, function ($x) { return strpos($x['name'], 'obj_') !== 0; });

        if (!$custom_phrases) {
            return array();
        }

        $phrase_groups = array();
        foreach ($custom_phrases as $phr) {
            if ($phr->groupname) {
                $phrase_groups[] = $phr->groupname;
            }
        }

        $default_phrases = array();
        $lang_phrases    = array();

        if ($phrase_groups && $language) {
            $phrase_groups = array_unique($phrase_groups);

            foreach ($phrase_groups as $group_id) {
                $default_phrases = array_merge($default_phrases,   $this->loadSystemPhrases('default', $group_id));
                $lang_phrases    = array_merge($lang_phrases,      $this->loadSystemPhrases($language->sys_name, $group_id));
            }
        }

        $phrase_data = array();

        foreach ($custom_phrases as $phr) {
            $id = $phr->name;
            $row = array(
                'id'      => $id,
                'default' => isset($default_phrases[$id]) ? $default_phrases[$id] : null,
                'lang'    => isset($lang_phrases[$id]) ? $lang_phrases[$id] : null,
                'custom'  => $phr->phrase,
            );

            // The default for the language
            $row['lang_default'] = $row['lang'] ?: $row['default'];

            // The actual set value that will be used
            $row['set'] = $row['custom'] ?: $row['lang_default'];

            $phrase_data[] = $row;
        }

        return $phrase_data;
    }


    /**
     * Returns a k=>v array of phrases from the system lang files
     *
     * @param  string $lang_name
     * @param  string $group_id
     * @return array
     */
    private function loadSystemPhrases($lang_name, $group_id)
    {
        // Simple cast to prevent bad input
        $group_id = preg_replace('#[^a-zA-Z0-9\.\-_]#', '', $group_id);

        $parts = explode('.', $group_id);
        if (count($parts) != 2) {
            return array();
        }

        $path = $this->lang_dir
            . DIRECTORY_SEPARATOR
            . $lang_name
            . DIRECTORY_SEPARATOR
            . $parts[0]
            . DIRECTORY_SEPARATOR
            . $parts[1]
            . '.php';

        if (!file_exists($path)) {
            return array();
        }

        $phrases = require($path);

        return $phrases;
    }

    /**
     * @param  Language $language
     * @param  string   $group_id
     * @return array
     */
    private function loadCustomPhrases(Language $language, $group_id)
    {
        return $this->phrase_repos->getPhrasesInGroup($language, $group_id);
    }
}
