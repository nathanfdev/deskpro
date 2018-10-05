<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Phrase;
use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\Languages\LangPackInfo;
use Application\DeskPRO\Languages\PhraseData;
use Application\DeskPRO\ResourceScanner\LanguagePhrases;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Numbers;

/**
 * @ApiModes("all")
 */
class LanguagesController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new PassPermission(), 'listAction');

        return $multi;
    }

    //###################################################################################################################
    // list
    //###################################################################################################################

    public function listAction()
    {
        $langs = $this->em->createQuery('
            SELECT l
            FROM DeskPRO:Language l
            ORDER BY l.title ASC
        ')->execute();

        $installed_packs = [];
        foreach ($langs as $l) {
            $installed_packs[$l->getSysName()] = $l;
        }

        $langpacks         = new \Application\DeskPRO\Languages\LangPackInfo();
        $pack_titles       = $langpacks->getLangTitles();
        $pack_local_titles = $langpacks->getLangTitles(true);

        foreach ($pack_titles as $id => $title) {
            if (strpos($id, 'dev_') === 0) {
                continue;
            }
            $lang = isset($installed_packs[$id]) ? $installed_packs[$id] : null;
            $info = $langpacks->getLangInfo($id);
            $r    = [
                'id'                    => $id,
                'title'                 => $title,
                'show_title'            => $lang ? $lang->title : $pack_local_titles[$id],
                'local_title'           => $pack_local_titles[$id],
                'flag'                  => 'locale_'.$info['locale'].'.png',
                'show_flag'             => false,
                'is_installed'          => $lang ? true : false,
                'installed_language_id' => $lang ? $lang->id : null,
                'has_user'              => true,
                'has_agent'             => true,
                'has_admin'             => true,
            ];

            $all_packs[] = $r;
        }

        $data['languages']       = $this->getApiData($langs);
        $data['packs']           = $all_packs;
        $data['default_lang_id'] = $this->container->getLanguageData()->getDefaultId();
        $data['is_multi_lang']   = $this->container->getLanguageData()->isMultiLang();

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // set-default-lang
    //###################################################################################################################

    public function setDefaultLangAction($id)
    {
        $langpacks = new LangPackInfo();

        if (Numbers::isInteger($id)) {
            $lang = $this->container->getLanguageData()->get($id);
            if (!$lang) {
                throw $this->createNotFoundException();
            }
        } else {
            if (!$langpacks->hasLang($id)) {
                throw $this->createNotFoundException();
            }

            $lang_info = $langpacks->getLangInfo($id);
            $lang      = null;

            foreach ($this->container->getLanguageData()->getAll() as $l) {
                if ($l->sys_name == $lang_info['id']) {
                    $lang = $l;
                    break;
                }
            }

            if (!$lang) {
                throw $this->createNotFoundException();
            }
        }

        $this->settings->setSetting('core.default_language_id', $lang->id);

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // get-lang
    //###################################################################################################################

    public function getLangAction($id)
    {
        $langpacks = new LangPackInfo();

        if (Numbers::isInteger($id)) {
            $lang = $this->container->getLanguageData()->get($id);
            if (!$lang) {
                throw $this->createNotFoundException();
            }

            $lang_info = $langpacks->getLangInfo($lang->sys_name);
        } else {
            if (!$langpacks->hasLang($id)) {
                throw $this->createNotFoundException();
            }

            $lang_info = $langpacks->getLangInfo($id);
            $lang      = null;

            foreach ($this->container->getLanguageData()->getAll() as $l) {
                if ($l->sys_name == $lang_info['id']) {
                    $lang = $l;
                    break;
                }
            }
        }

        if ($lang) {
            $lang_info['is_installed'] = true;
        }

        $flag                     = $langpacks->getLangInfo($id, 'flag_image');
        $pack_local_titles        = $langpacks->getLangTitles(true);
        $lang_info['show_title']  = $lang ? $lang->title : $pack_local_titles[$lang_info['id']];
        $lang_info['local_title'] = $pack_local_titles[$id];
        $lang_info['flag']        = $flag;
        $lang_info['show_flag']   = $lang ? $lang->flag_image : $lang_info['flag'];

        return $this->createApiResponse([
            'pack'     => $lang_info,
            'language' => $lang ? $lang->toApiData() : null,
        ]);
    }

    //###################################################################################################################
    // get-phrase
    //###################################################################################################################

    public function getPhraseAction($phrase_id, $for_lang = -1)
    {
        if ($for_lang != -1) {
            $lang = $this->em->find(Language::class, $for_lang);
            if (!$lang) {
                throw ValidationException::create('for_lang.invalid', 'Invalid lanugage specified');
            }

            $phrase = $this->em->getRepository(Phrase::class)->getPhraseForLanguage($phrase_id, $lang);

            if ($phrase) {
                $data['phrase'] = $phrase->toApiData(false);
            } else {
                $data['phrase'] = null;
            }
        } else {
            $langs = $this->em->createQuery('
                SELECT l
                FROM DeskPRO:Language l
                ORDER BY l.title ASC
            ')->execute();

            $data['lang_phrases'] = [];
            foreach ($langs as $lang) {
                $phrase = $this->em->getRepository(Phrase::class)->getPhraseForLanguage($phrase_id, $lang);

                if ($phrase) {
                    $data['lang_phrases'][] = $phrase->toApiData(true);
                }
            }
        }

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // install-lang
    //###################################################################################################################

    public function installLangAction($id)
    {
        $langpacks = new LangPackInfo();

        if (!$langpacks->hasLang($id)) {
            throw $this->createNotFoundException();
        }

        $lang_info = $langpacks->getLangInfo($id);

        foreach ($this->container->getLanguageData()->getAll() as $l) {
            if ($l->sys_name == $lang_info['id']) {
                return $this->createApiErrorResponse('exists', "$id is already installed");
            }
        }

        $lang = $langpacks->newLanguageEntity($id);

        $this->db->beginTransaction();
        try {
            $this->em->persist($lang);
            $this->em->flush();
            $this->db->commit();

            foreach ($this->em->getRepository(Language::class)->findAll() as $_lang) {
                $this->em->getConnection()->executeQuery(sprintf('
                insert ignore into object_lang (language_id, ref, ref_type, ref_id, prop_name, value)
                (
                    select %d, concat("text_snippet_categories.", ref_id), "text_snippet_categories", ref_id, "title", value
                    from object_lang
                    where ref_type = "text_snippet_categories"
                    group by ref_id
                )
            ', $_lang->getId()));
            }
        } catch (\Exception $e) {
            $this->db->rollback();
        }

        return $this->createApiCreateResponse([
            'pack_id'     => $id,
            'language_id' => $lang->id,
        ], $this->generateUrl('api_langs_getinfo', ['id' => $lang->id]));
    }

    //###################################################################################################################
    // uninstall-lang
    //###################################################################################################################

    public function uninstallLangAction($id)
    {
        $langpacks = new LangPackInfo();

        if (Numbers::isInteger($id)) {
            $lang = $this->container->getLanguageData()->get($id);
            if (!$lang) {
                throw $this->createNotFoundException();
            }

            $lang_info = $langpacks->getLangInfo($lang->sys_name);
        } else {
            if (!$langpacks->hasLang($id)) {
                throw $this->createNotFoundException();
            }

            $lang_info = $langpacks->getLangInfo($id);
            $lang      = null;

            foreach ($this->container->getLanguageData()->getAll() as $l) {
                if ($l->sys_name == $lang_info['id']) {
                    $lang = $l;
                    break;
                }
            }

            if (!$lang) {
                throw $this->createNotFoundException();
            }
        }

        $default_id = $this->container->getLanguageData()->getDefaultId();
        if ($default_id == $lang->id) {
            return $this->createApiErrorResponse('no_delete_default', 'You cannot delete the default language');
        }

        $old_lang_id = $lang->id;

        $this->em->remove($lang);
        $this->em->flush();

        return $this->createSuccessResponse([
            'old_pack_id'     => $lang_info['id'],
            'old_language_id' => $old_lang_id,
        ]);
    }

    //###################################################################################################################
    // save-lang
    //###################################################################################################################

    public function saveLangAction($id)
    {
        $langpacks = new LangPackInfo();

        if (Numbers::isInteger($id)) {
            $lang = $this->container->getLanguageData()->get($id);
            if (!$lang) {
                throw $this->createNotFoundException();
            }

            $lang_info = $langpacks->getLangInfo($lang->sys_name);
        } else {
            if (!$langpacks->hasLang($id)) {
                throw $this->createNotFoundException();
            }

            $lang_info = $langpacks->getLangInfo($id);
            $lang      = null;

            foreach ($this->container->getLanguageData()->getAll() as $l) {
                if ($l->sys_name == $lang_info['id']) {
                    $lang = $l;
                    break;
                }
            }

            if (!$lang) {
                throw $this->createNotFoundException();
            }
        }

        $lang->title      = $this->in->getString('language.title') ?: $lang_info['title'];
        $lang->locale     = $this->in->getString('language.locale') ?: $lang['locale'];
        $lang->flag_image = $this->in->getString('language.flag_image') ?: $lang['flag_image'];

        if (!file_exists(DP_WEB_ROOT.'/web/images/flags/'.$lang->flag_image)) {
            $lang->flag_image = $lang['flag_image'];
        }

        $this->em->persist($lang);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // save-phrase
    //###################################################################################################################

    public function savePhraseAction($phrase_id)
    {
        $langs = $this->em->createQuery('
            SELECT l
            FROM DeskPRO:Language l INDEX BY l.id
            ORDER BY l.title ASC
        ')->execute();

        foreach ($this->in->getArrayValue('lang_phrases') as $lang_phrase) {
            $phrase_text = trim($lang_phrase['phrase']);
            $lang_id     = intval($lang_phrase['language_id']);

            if (!isset($langs[$lang_id])) {
                continue;
            }

            $lang = $langs[$lang_id];

            $phrase = $this->em->getRepository(Phrase::class)->getPhraseForLanguage($phrase_id, $lang);
            if (!$phrase_text) {
                if ($phrase) {
                    $this->em->remove($phrase);
                }
            } else {
                if (!$phrase) {
                    $phrase                  = new Phrase();
                    $phrase->language        = $lang;
                    $phrase->name            = $phrase_id;
                    $phrase->original_phrase = '';
                    $phrase->original_hash   = md5(null);
                }

                if (!$phrase->original_phrase) {
                    $phrase->original_phrase = '';
                    $phrase->original_hash   = md5(null);
                }
                $phrase->phrase = $phrase_text;
                $this->em->persist($phrase);
            }
        }

        $this->em->flush();

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // save-phrase-set
    //###################################################################################################################

    public function savePhraseSetAction($id)
    {
        if (Numbers::isInteger($id)) {
            $lang = $this->container->getLanguageData()->get($id);
            if (!$lang) {
                throw $this->createNotFoundException();
            }
        } else {
            $langpacks = new LangPackInfo();
            if (!$langpacks->hasLang($id)) {
                throw $this->createNotFoundException();
            }

            $lang_info = $langpacks->getLangInfo($id);
            $lang      = null;

            foreach ($this->container->getLanguageData()->getAll() as $l) {
                if ($l->sys_name == $lang_info['id']) {
                    $lang = $l;
                    break;
                }
            }

            if (!$lang) {
                throw $this->createNotFoundException();
            }
        }

        $adds       = [];
        $phrase_ids = [];

        foreach ($this->in->getArrayValue('phrases') as $phrase_info) {
            if (empty($phrase_info['name']) || !preg_match('#^[a-zA-Z0-9\.\-_]+$#', $phrase_info['name'])) {
                continue;
            }

            if (!isset($phrase_info['phrase'])) {
                $phrase_info['phrase'] = null;
            }

            $phrase_id = $phrase_info['name'];
            $phrase    = $phrase_info['phrase'];

            $phrase_ids[] = $phrase_id;
            if ($phrase != '' && $phrase !== null) {
                $p = new Phrase();
                $p->setName($phrase_id);
                $p->phrase = $phrase;

                $adds[] = [
                    'language_id'     => $lang->id,
                    'name'            => $p->name,
                    'groupname'       => $p->groupname,
                    'phrase'          => $p->phrase,
                    'original_phrase' => $p->original_phrase,
                    'original_hash'   => $p->original_hash,
                    'is_outdated'     => (int) $p->is_outdated,
                    'created_at'      => $p->created_at ? $p->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at'      => $p->updated_at ? $p->updated_at->format('Y-m-d H:i:s') : null,
                ];
            }
        }

        if ($phrase_ids) {
            $this->db->deleteIn('phrases', $phrase_ids, 'name', false, "language_id = {$lang->id}");

            if ($adds) {
                $this->db->batchInsert('phrases', $adds, true);
            }
        }

        return $this->createSuccessResponse();
    }

    //###########################################################################
    // get-phrase-groups
    //###########################################################################

    public function getPhraseGroupsAction()
    {
        //------------------------------
        // Special object groups
        //------------------------------

        $object_groups   = [];
        $object_groups[] = ['id' => 'ticket_departments',  'title' => 'Ticket Departments'];
        $object_groups[] = ['id' => 'chat_departments',    'title' => 'Chat Departments'];

        if ($this->settings->get('core.use_product')) {
            $object_groups[] = ['id' => 'products',            'title' => 'Products'];
        }
        if ($this->settings->get('core.use_ticket_category')) {
            $object_groups[] = ['id' => 'ticket_categories',   'title' => 'Ticket Categories'];
        }
        if ($this->settings->get('core.use_ticket_priority')) {
            $object_groups[] = ['id' => 'ticket_priorities',   'title' => 'Ticket Priorities'];
        }
        if ($this->settings->get('core.use_ticket_workflow')) {
            $object_groups[] = ['id' => 'ticket_workflows',    'title' => 'Ticket Workflows'];
        }

        $object_groups[] = ['id' => 'feedback_statuses',   'title' => 'Feedback Statuses'];
        $object_groups[] = ['id' => 'feedback_types',      'title' => 'Feedback Types'];
        $object_groups[] = ['id' => 'kb_categories',       'title' => 'Knowledgebase Categories'];

        if ($this->container->getSystemService('ticket_fields_manager')->count()) {
            $object_groups[] = ['id' => 'ticket_fields',       'title' => 'Ticket Fields'];
        }
        if ($this->container->getSystemService('person_fields_manager')->count()) {
            $object_groups[] = ['id' => 'person_fields',       'title' => 'Person Fields'];
        }
        if ($this->container->getSystemService('org_fields_manager')->count()) {
            $object_groups[] = ['id' => 'org_fields',          'title' => 'Organization Fields'];
        }
        if ($this->container->getSystemService('chat_fields_manager')->count()) {
            $object_groups[] = ['id' => 'chat_fields',         'title' => 'Chat Fields'];
        }

        //------------------------------
        // Phrase groups
        //------------------------------

        $groups_reader = new LanguagePhrases();
        $tr            = $this->container->getTranslator();
        $phrase_groups = [];

        foreach ($groups_reader->getGroups() as $type => $groups) {
            $phrase_groups[$type] = [];

            foreach ($groups as $group) {
                $phrase_id = "admin.languages.phrasegroup_{$type}_{$group}";
                $title     = $tr->hasPhrase($phrase_id) ? $tr->phrase($phrase_id) : ucfirst("$type $group");

                $phrase_groups[$type][] = ['id' => "{$type}.{$group}", 'title' => $title];
            }
        }

        return $this->createJsonResponse([
            'phrase_groups' => [
                'object' => $object_groups,
                'user'   => $phrase_groups['portal'],
                'agent'  => $phrase_groups['agent'],
                'admin'  => $phrase_groups['admin'],
            ],
        ]);
    }

    //###########################################################################
    // get-phrases
    //###########################################################################

    public function getPhrasesAction($id, $group_id)
    {
        if (Numbers::isInteger($id)) {
            $lang = $this->container->getLanguageData()->get($id);
            if (!$lang) {
                throw $this->createNotFoundException();
            }
        } else {
            $langpacks = new LangPackInfo();
            if (!$langpacks->hasLang($id)) {
                throw $this->createNotFoundException();
            }

            $lang_info = $langpacks->getLangInfo($id);
            $lang      = null;

            foreach ($this->container->getLanguageData()->getAll() as $l) {
                if ($l->sys_name == $lang_info['id']) {
                    $lang = $l;
                    break;
                }
            }

            if (!$lang) {
                $lang = null;
            }
        }

        /** @var \Application\DeskPRO\EntityRepository\Phrase $repos */
        $repos = $this->em->getRepository(Phrase::class);

        $phrase_data = new PhraseData($repos, DP_ROOT.'/languages');

        switch ($group_id) {
            case 'ticket_departments':
                $phrases = $phrase_data->getTicketDepartmentPhrases(
                    $this->container->getSystemService('ticket_departments'),
                    $lang
                );
                break;

            case 'ticket_categories':
                $phrases = $phrase_data->getTicketCategoryPhrases(
                    $this->container->getSystemService('ticket_categories'),
                    $lang
                );
                break;

            case 'ticket_priorities':
                $phrases = $phrase_data->getTicketPriorityPhrases(
                    $this->container->getSystemService('ticket_priorities'),
                    $lang
                );
                break;

            case 'chat_departments':
                $phrases = $phrase_data->getChatDepartmentPhrases(
                    $this->container->getSystemService('chat_departments'),
                    $lang
                );
                break;

            case 'products':
                $phrases = $phrase_data->getProductPhrases(
                    $this->container->getSystemService('products'),
                    $lang
                );
                break;

            case 'ticket_fields':
                $phrases = $phrase_data->getFieldPhrases(
                    $this->container->getSystemService('ticket_fields_manager'),
                    $lang
                );
                break;

            case 'person_fields':
                $phrases = $phrase_data->getFieldPhrases(
                    $this->container->getSystemService('person_fields_manager'),
                    $lang
                );
                break;

            case 'org_fields':
                $phrases = $phrase_data->getFieldPhrases(
                    $this->container->getSystemService('org_fields_manager'),
                    $lang
                );
                break;

            case 'chat_fields':
                $phrases = $phrase_data->getFieldPhrases(
                    $this->container->getSystemService('chat_fields_manager'),
                    $lang
                );
                break;

            case 'feedback_statuses':
                $phrases = $phrase_data->getFeedbackStatusPhrases($lang);
                break;

            case 'feedback_types':
                $phrases = $phrase_data->getFeedbackTypePhrases($lang);
                break;

            case 'kb_categories':
                /** @var \Application\DeskPRO\EntityRepository\ArticleCategory $repos */
                $repos   = $this->em->getRepository(ArticleCategory::class);
                $phrases = $phrase_data->getKbCategoryPhrases($repos, $lang);
                break;

            case 'custom':
                $phrases = $phrase_data->loadCustom($lang);
                break;

            default:
                $phrases = $phrase_data->loadGroup($lang, $group_id);
                break;
        }

        return $this->createJsonResponse([
            'phrases' => $phrases,
        ]);
    }

    //###########################################################################
    // mass-update-tickets
    //###########################################################################

    public function massUpdateTicketsAction()
    {
        $from_lang_id = $this->in->getUint('from_lang');
        $to_lang_id   = $this->in->getUint('to_lang');

        if (
            ($from_lang_id && !$this->container->getLanguageData()->get($from_lang_id))
            || ($to_lang_id && !$this->container->getLanguageData()->get($to_lang_id))
        ) {
            throw $this->createNotFoundException();
        }

        $use_to_id = $to_lang_id;
        if (!$use_to_id) {
            $use_to_id = 'NULL';
        }

        $sql = "UPDATE tickets SET language_id = $use_to_id";
        if ($from_lang_id) {
            $sql .= " WHERE language_id = $from_lang_id";
        }
        $count = $this->db->executeUpdate($sql);

        $sql = "UPDATE tickets_search_active SET language_id = $use_to_id";
        if ($from_lang_id) {
            $sql .= " WHERE language_id = $from_lang_id";
        }
        $this->db->executeUpdate($sql);

        return $this->createSuccessResponse([
            'count' => $count,
        ]);
    }

    //###########################################################################
    // mass-update-users
    //###########################################################################

    public function massUpdateUsersAction()
    {
        $from_lang_id = $this->in->getUint('from_lang');
        $to_lang_id   = $this->in->getUint('to_lang');

        if (
            ($from_lang_id && !$this->container->getLanguageData()->get($from_lang_id))
            || ($to_lang_id && !$this->container->getLanguageData()->get($to_lang_id))
        ) {
            throw $this->createNotFoundException();
        }

        $use_to_id = $to_lang_id;
        if (!$use_to_id) {
            $use_to_id = 'NULL';
        }

        $sql = "UPDATE people SET language_id = $use_to_id";
        if ($from_lang_id) {
            $sql .= " WHERE language_id = $from_lang_id";
        }
        $count = $this->db->executeUpdate($sql);

        return $this->createSuccessResponse([
            'count' => $count,
        ]);
    }
}
