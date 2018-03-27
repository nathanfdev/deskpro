<?php

namespace Application\AgentBundle\Controller;

use Application\AgentBundle\Controller\Helper\PeopleResults;
use Application\AgentBundle\Controller\JsonRenderer\PeopleListRenderer;
use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\BanEmail;
use Application\DeskPRO\Entity\LabelDef;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ResultCache;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\EntityRepository\AgentTeam as AgentTeamRepository;
use Application\DeskPRO\EntityRepository\BanEmail as BanEmailRepository;
use Application\DeskPRO\EntityRepository\LabelDef as LabelDefRepository;
use Application\DeskPRO\EntityRepository\Organization as OrganizationRepository;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\DeskPRO\EntityRepository\Usergroup as UsergroupRepository;
use Application\DeskPRO\Labels\LabelLister;
use Application\DeskPRO\People\PeopleResultsDisplay;
use Application\DeskPRO\Searcher\PersonSearch;
use Application\DeskPRO\UI\RuleBuilder;
use Application\DeskPRO\UI\TagCloud;
use DpSys\LowError\SystemErrorHandler;
use Orb\Util\Arrays;
use Orb\Util\Strings;
use Orb\Validator\StringEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles searching for people.
 */
class PeopleSearchController extends AbstractController
{
    public function getSectionDataAction()
    {
        $data = [];

        /** @var PersonRepository $personRepository */
        $personRepository = $this->em->getRepository(Person::class);
        /** @var LabelDefRepository $labelDefRepository */
        $labelDefRepository = $this->em->getRepository(LabelDef::class);
        /** @var AgentTeamRepository $agentTeamRepository */
        $agentTeamRepository = $this->em->getRepository(AgentTeam::class);
        /** @var OrganizationRepository $organizationRepository */
        $organizationRepository = $this->em->getRepository(Organization::class);
        /** @var UsergroupRepository $usergroupRepository */
        $usergroupRepository = $this->em->getRepository(Usergroup::class);

        //------------------------------
        // People labels
        //------------------------------
        $people_count = $this->settings->get('core_tablecounts.people');
        if ($people_count < 10000) {
            $people_count = $personRepository->getCount(true);
        }

        $label_counts     = $labelDefRepository->getLabelCounts('people', 25);
        $cloud_gen        = new TagCloud($label_counts);
        $people_tag_cloud = $cloud_gen->getCloud();

        $label_lister     = new LabelLister('people');
        $people_tag_index = $label_lister->getIndexList();

        //------------------------------
        // Agents and teams
        //------------------------------
        $team_names  = $agentTeamRepository->getTeamNames();
        $team_counts = $agentTeamRepository->getTeamCounts();
        $agent_count = count($personRepository->getAgents());

        //------------------------------
        // Org labels
        //------------------------------

        $org_count = $organizationRepository->getCount();

        $label_counts  = $labelDefRepository->getLabelCounts('organizations', 25);
        $cloud_gen     = new TagCloud($label_counts);
        $org_tag_cloud = $cloud_gen->getCloud();

        $label_lister  = new LabelLister('organizations');
        $org_tag_index = $label_lister->getIndexList();

        $usergroup_names  = $usergroupRepository->getUsergroupNames();
        $usergroup_counts = $usergroupRepository->getCountsFor(array_keys($usergroup_names));

        $data['section_html'] = $this->renderView('AgentBundle:PeopleSearch:window-section.html.twig', [
            'usergroup_names'  => $usergroup_names,
            'usergroup_counts' => $usergroup_counts,

            'team_names'  => $team_names,
            'team_counts' => $team_counts,
            'agent_count' => $agent_count,

            'people_count'     => $people_count,
            'people_tag_cloud' => $people_tag_cloud,
            'people_tag_index' => $people_tag_index,
            'org_tag_cloud'    => $org_tag_cloud,
            'org_tag_index'    => $org_tag_index,
            'org_count'        => $org_count,
        ]);

        return $this->createJsonResponse($data);
    }

    public function reloadCountsAction()
    {
        $people_count = $this->settings->get('core_tablecounts.people');
        /** @var UsergroupRepository $usergroupRepository */
        $usergroupRepository = $this->em->getRepository('DeskPRO:Usergroup');

        if ($people_count < 10000) {
            /** @var PersonRepository $personRepository */
            $personRepository = $this->em->getRepository(Person::class);
            $people_count     = $personRepository->getCount(true);
        }

        $data = [
            'people_count'     => $people_count,
            'usergroup_counts' => $usergroupRepository->getCountsForAll(),
        ];

        return $this->createJsonResponse($data);
    }

    public function reloadLabelDataAction()
    {
        /** @var LabelDefRepository $labelDefRepository */
        $labelDefRepository = $this->em->getRepository(LabelDef::class);

        // People
        $label_counts     = $labelDefRepository->getLabelCounts('people', 25);
        $cloud_gen        = new TagCloud($label_counts);
        $people_tag_cloud = $cloud_gen->getCloud();

        $label_lister     = new LabelLister('people');
        $people_tag_index = $label_lister->getIndexList();

        // Orgs
        $label_counts  = $labelDefRepository->getLabelCounts('organizations', 25);
        $cloud_gen     = new TagCloud($label_counts);
        $org_tag_cloud = $cloud_gen->getCloud();

        $label_lister  = new LabelLister('organizations');
        $org_tag_index = $label_lister->getIndexList();

        $data                       = [];
        $data['people_label_cloud'] = $this->renderView('AgentBundle:PeopleSearch:window-people-label-cloud.html.twig', ['people_tag_cloud' => $people_tag_cloud]);
        $data['people_label_list']  = $this->renderView('AgentBundle:PeopleSearch:window-people-label-list.html.twig', ['people_tag_index' => $people_tag_index]);
        $data['org_label_cloud']    = $this->renderView('AgentBundle:PeopleSearch:window-org-label-cloud.html.twig', ['org_tag_cloud' => $org_tag_cloud]);
        $data['org_label_list']     = $this->renderView('AgentBundle:PeopleSearch:window-org-label-list.html.twig', ['org_tag_index' => $org_tag_index]);

        return $this->createJsonResponse($data);
    }

    protected function _getResponseForPeople($type, $type_id, PeopleResults $results_helper, array $vars = [])
    {
        $view_type = $this->in->getString('view_type');
        if (!$view_type or !in_array($view_type, ['list', 'simple', 'json'])) {
            $view_type = 'simple';
        }

        $is_partial = false;
        $tpl        = 'AgentBundle:PeopleSearch:'.$type.($view_type != 'simple' ? '-'.$view_type : '').'.html.twig';
        if ($this->in->getBool('partial')) {
            $is_partial = true;
            $tpl        = 'AgentBundle:PeopleSearch:'.$type.'-page'.($view_type != 'simple' ? '-'.$view_type : '').'.html.twig';
        }

        //------------------------------
        // Get the tickets to show
        //------------------------------

        $page = $this->in->getUint('page');
        if (!$page) {
            $page = 1;
        }

        $people = $results_helper->getPeopleForPage($page);

        //------------------------------
        // Send results
        //------------------------------

        if (!count($people) && $is_partial) {
            return $this->createJsonResponse(['no_more_results' => true]);
        }

        $vars['display_fields'] = Arrays::removeFalsey($vars['display_fields']);
        $vars['display_fields'] = array_unique($vars['display_fields']);

        $alphabet = $this->getAlphabet();
        $letters  = [];

        $params           = $_GET;
        $params['letter'] = '*';
        $letters[]        = ['title' => '*', 'params' => $params];
        $params['letter'] = '#';
        $letters[]        = ['title' => '#', 'params' => $params];

        foreach ($alphabet as $letter) {
            $params['letter'] = $letter;
            $letters[]        = [
                'title'  => $letter,
                'params' => $params,
            ];
        }

        $person_display = new PeopleResultsDisplay($people, $this->getPerson());
        $renderer       = new PeopleListRenderer($this->container);

        $vars = array_merge($vars, [
            'type'        => $type,
            'type_id'     => $type_id,
            'people'      => $people,
            'people_json' => $renderer->renderJson($person_display),
            'page'        => $page,
            'per_page'    => $results_helper->getPerPageCount(),
            'load_first'  => $this->in->getBool('load_first'),
            'alphabet'    => $letters,
        ]);

        if ('json' === $view_type) {
            return $this->createJsonpResponse($vars);
        } else {
            $html = $this->renderView($tpl, $vars);

            if ($is_partial) {
                return $this->createJsonResponse([
                    'html' => $html,
                    'page' => $page,
                ]);
            } else {
                return $this->createResponse($html);
            }
        }
    }

    /**
     * Render a new pageset.
     *
     * @return Response
     */
    public function getPeoplePageAction()
    {
        $person_ids = $this->in->getCleanValueArray('result_ids', 'uint', 'discard');
        $person_ids = Arrays::removeFalsey($person_ids);
        $person_ids = array_unique($person_ids);

        /** @var PersonRepository $personRepository */
        $personRepository = $this->em->getRepository(Person::class);
        $people           = $personRepository->getPeopleResultsFromIds($person_ids);
        $people           = Arrays::orderIdArray($person_ids, $people);

        $display_fields = $this->in->getCleanValueArray('display_fields', 'string', 'discard');
        $display_fields = Arrays::removeFalsey($display_fields);
        $display_fields = array_unique($display_fields);

        $user_field_manager = $this->container->getSystemService('person_fields_manager');
        $person_field_defs  = $user_field_manager->getFields();

        $view_type = $this->in->getString('view_type');
        $tpl       = 'list-page.html.twig';
        if ('list' === $view_type) {
            $tpl = 'list-list-page.html.twig';
        } elseif ('json' === $view_type) {
            $person_display = new PeopleResultsDisplay($people, $this->getPerson());
            $renderer       = new PeopleListRenderer($this->container);

            return $this->createJsonResponse($renderer->renderArray($person_display));
        }

        $result_display = new PeopleResultsDisplay($people, $this->getPerson());

        return $this->render("AgentBundle:PeopleSearch:$tpl", [
            'people'            => $people,
            'display_fields'    => $display_fields,
            'person_field_defs' => $person_field_defs,
            'result_display'    => $result_display,
        ]);
    }

    //###########################################################################
    // search
    //###########################################################################

    public function searchAction($letter, $use_terms = null, $set_view_name = null)
    {
        $result_cache = false;
        if ($this->in->getUint('cache_id')) {
            $result_cache = $this->em->getRepository(ResultCache::class)->find($this->in->getUint('cache_id'));
            if (!$result_cache or $result_cache['person_id'] != $this->person['id']) {
                $result_cache = false;
            }
        }

        $user_letter = $this->getLetterFromUser($letter);

        //------------------------------
        // If there's no result set, we're running it for the first time
        //------------------------------

        if (!$result_cache || $user_letter != $result_cache['criteria']['selected_letter']) {
            $old_result_cache = false;
            if ($this->in->getUint('copy_display_options')) {
                $old_result_cache = $this->em->getRepository(ResultCache::class)->find($this->in->getUint('copy_display_options'));
                if (!$old_result_cache or $old_result_cache['person_id'] != $this->person['id']) {
                    $old_result_cache = false;
                }
            }

            $term_rules = RuleBuilder::newTermsBuilder();
            $terms      = $term_rules->readForm($this->in->getCleanValueArray('terms', 'raw', 'discard'));

            $set_terms_map = [
                'person_organization'  => ['op' => 'contains', 'options' => []],
                'person_usergroup'     => ['op' => 'contains', 'options' => []],
                'person_label'         => ['op' => 'contains', 'options' => []],
                'person_name'          => ['op' => 'contains', 'options' => []],
                'person_email'         => ['op' => 'contains', 'options' => []],
                'person_contact_phone' => ['op' => 'contains', 'options' => []],
                'is_confirmed'         => ['op' => 'is', 'options' => []],
                'any_mode'             => ['op' => 'is', 'options' => []],
            ];

            foreach ($set_terms_map as $name => $info) {
                $in_val = $this->container->getIn()->getCleanValue('set_term.'.$name, 'raw');
                if (!$in_val && $use_terms && isset($use_terms[$name])) {
                    $in_val = $use_terms[$name];
                }
                if (is_string($in_val)) {
                    $in_val = trim($in_val);
                } elseif (is_array($in_val)) {
                    $in_val = Arrays::removeEmptyString($in_val);
                }
                if ($in_val || isset($use_terms[$name])) {
                    $new_term            = $info;
                    $new_term['options'] = $in_val;
                    Arrays::unshiftAssoc($new_term, 'type', $name);
                    $terms[] = $new_term;
                }
            }

            $set_custom_fields = $this->container->getIn()->getCleanValueArray('set_custom_field', 'raw', 'string');

            foreach ($set_custom_fields as $field_name => $field_value) {
                if (is_array($field_value)) {
                    $field_value = Arrays::removeFalsey($field_value);
                }

                if (!$field_value) {
                    continue;
                }

                $id = Strings::extractRegexMatch('#field_([0-9]+)#', $field_name, 1);
                if (!$id) {
                    continue;
                }

                $new_term = [
                    'type'    => "person_field[$id]",
                    'op'      => 'is',
                    'options' => ['value' => $field_value],
                ];

                $terms[] = $new_term;
            }

            $searcher = new PersonSearch();

            $selected_letter = $this->applyLetterToSearcher($user_letter, $searcher);

            if ($search_val = $this->in->getString('person_name')) {
                $searcher->addTerm('person_name', 'contains', $search_val);
            }
            if ($search_val = $this->in->getString('person_contact_phone')) {
                $searcher->addTerm('person_contact_phone', 'contains', $search_val);
            }
            if ($search_val = $this->in->getString('person_email')) {
                $searcher->addTerm('person_email', 'contains', $search_val);
            }
            if ($search_val = $this->in->getString('person_organization_name')) {
                $searcher->addTerm('person_organization_name', 'contains', $search_val);
            }
            if ($search_val = $this->in->getString('person_ip')) {
                $searcher->addTerm('person_ip', 'contains', $search_val);
            }
            if ($search_val = $this->in->getString('person_label')) {
                $search_val = explode(',', $search_val);
                $search_val = Arrays::func($search_val, 'trim');
                $search_val = Arrays::removeFalsey($search_val);

                if ($search_val) {
                    $searcher->addTerm('person_label', 'contains', $search_val);
                }
            }
            if ($search_val = $this->in->getCleanValueArray('person_usergroup', 'uint', 'discard')) {
                $search_val = Arrays::removeFalsey($search_val);

                if ($search_val) {
                    $searcher->addTerm('person_usergroup', 'is', $search_val);
                }
            }

            foreach ($terms as $term) {
                $searcher->addTerm($term['type'], $term['op'], $term['options']);
            }

            $order_by = $this->person->getPref('agent.ui.people-filter-order-by.0');

            if (!$order_by) {
                $order_by = 'people.id:asc';
            }

            if ($order_by) {
                $searcher->setOrderByCode($order_by);
            }

            $results = $searcher->getMatches();

            $result_cache                = new ResultCache();
            $result_cache['person']      = $this->person;
            $result_cache['criteria']    = ['terms' => $searcher->getTerms(), 'order_by' => $order_by, 'selected_letter' => $selected_letter];
            $result_cache['results']     = $results;
            $result_cache['num_results'] = count($results);
            $result_cache->setExtraData('terms_summary', $searcher->getSummary());

            if ($old_result_cache) {
                $result_cache['extra'] = $old_result_cache['extra'];
            }

            $this->em->persist($result_cache);
            $this->em->flush();
        }

        //------------------------------
        // Re-do search if we changed order
        //------------------------------

        // Prefs are saved into extra[]. Of order_by doesn't match
        // the order_by in criteria, that means the user changed it
        // and we have to re-do the search

        $order_pref = $this->person->getPref('agent.ui.people-filter-order-by.'. 0);

        if (($order_pref && $order_pref != $result_cache['criteria']['order_by'])
        || $user_letter != $result_cache['criteria']['selected_letter']) {
            $searcher = new PersonSearch();

            $criteria                    = $result_cache['criteria'];
            $criteria['order_by']        = $order_pref;
            $criteria['selected_letter'] = $this->applyLetterToSearcher($user_letter, $searcher);

            $result_cache['criteria'] = $criteria;

            $searcher->setTerms($result_cache['criteria']['terms']);
            $searcher->setOrderByCode($result_cache['criteria']['order_by']);

            $results                     = $searcher->getMatches();
            $result_cache['results']     = $results;
            $result_cache['num_results'] = count($results);
            $result_cache->setExtraData('terms_summary', $searcher->getSummary());

            $this->em->persist($result_cache);
            $this->em->flush();
        }

        //------------------------------
        // Serve results
        //------------------------------

        $results_helper = PeopleResults::newFromResultCache($this, $result_cache);

        $vars = [
            'cache'           => $result_cache,
            'cache_id'        => $result_cache['id'],
            'person_ids'      => $result_cache['results'],
            'terms_summary'   => $result_cache->getExtraData('terms_summary'),
            'selected_letter' => $result_cache['criteria']['selected_letter'],
        ];

        if (!empty($result_cache['extra']['display_fields'])) {
            $vars['display_fields'] = $result_cache['extra']['display_fields'];
        }

        $pref_display_fields = $this->person->getPref('agent.ui.people-filter-display-fields.'.$result_cache['id']);
        if ($pref_display_fields) {
            $vars['display_fields'] = $pref_display_fields;
        } else {
            $pref_display_fields    = $this->person->getPref('agent.ui.people-filter-display-fields.0');
            $vars['display_fields'] = $pref_display_fields;
        }

        if ($this->in->getString('page_title')) {
            $vars['page_title'] = $this->in->getString('page_title');
        }

        if (!$vars['display_fields']) {
            $vars['display_fields'] = ['name', 'email', 'org', 'org_pos', 'num_tickets'];
        }

        $vars['preselect_terms'] = $result_cache['criteria'];
        $vars['num_results']     = $result_cache['num_results'];

        // Used in the search form again
        $titles                  = [];
        $titles['organizations'] = $this->container->getDataService('Organization')->getOrganizationNames();
        $titles['usergroups']    = $this->container->getDataService('Usergroup')->getUsergroupNames();

        if ($this->container->getDataService('Language')->isMultiLang()) {
            $titles['languages'] = $this->container->getDataService('Language')->getTitles();
        }

        $vars['titles'] = $titles;

        if (!$set_view_name) {
            $set_view_name = $this->in->getStrSimple('view_name');
        }

        if ($set_view_name) {
            $vars['view_name'] = $set_view_name;

            if (strpos($vars['view_name'], '.') !== false) {
                list($view_name_type, $view_name_data) = explode('.', $vars['view_name'], 2);
                $vars['view_name_type']                = $view_name_type;
                $vars['view_name_data']                = (int) $view_name_data;
            }
        }

        return $this->_getResponseForPeople('list', $result_cache['id'], $results_helper, $vars);
    }

    public function showUsergroupAction($id)
    {
        $usergroup = $this->em->find('DeskPRO:Usergroup', $id);
        if (!$usergroup || $usergroup->is_agent_group) {
            throw new NotFoundHttpException();
        }

        return $this->searchAction('*', ['person_usergroup' => $id], 'usergroup.'.$id);
    }

    public function showOrganizationMembersAction($id)
    {
        $organization = $this->em->find('DeskPRO:Organization', $id);
        if (!$organization) {
            throw new NotFoundHttpException();
        }

        return $this->searchAction('*', ['person_organization' => $id, 'any_mode' => 1], 'organization.'.$id);
    }

    protected function applyLetterToSearcher($letter, $searcher)
    {
        $selected_letter = '*';

        switch ($letter) {
            case '#':
                $selected_letter = '#';
                $searcher->addTerm('alphabetical', 'contains', $this->getAlphabet(true));
            case '*':
                break;
            default:
                $selected_letter = $letter;
                $searcher->addTerm('alphabetical', 'contains', [$letter, strtolower($letter)]);
                break;
        }

        return $selected_letter;
    }

    protected function getLetterFromUser($letter = null)
    {
        if ($letter === null) {
            $letter = $this->in->getString('letter');
        }

        if (is_string($letter) && strlen($letter) == 1) {
            if ($letter == '#') {
                return '#';
            } else {
                $alphabet = $this->getAlphabet();

                if (in_array($letter, $alphabet)) {
                    return $letter;
                }
            }
        }

        return '*';
    }

    protected function getAlphabet($numbers = false)
    {
        if ($numbers) {
            $numbers = [];

            for ($i = 0; $i < 10; ++$i) {
                $numbers[] = $i;
            }

            return $numbers;
        }

        $letters = [];

        for ($i = ord('A'); $i <= ord('Z'); ++$i) {
            $letters[] = chr($i);
        }

        return $letters;
    }

    /* TODO
    public function massActionsAction($action)
    {
        $this->em->beginTransaction();

        $people = $this->em->getRepository('DeskPRO:Person')->getByIds($this->in->getCleanValueArray('ids', 'uint', 'discard'));

        $organization = null;
        $usergroup = null;

        if ($this->in->getUint('organization_id')) {
            $organization = $this->em->find('DeskPRO:Organization', $this->in->getUint('organization_id'));
        }
        if ($this->in->getUint('usergroup_id')) {
            $usergroup = $this->em->find('DeskPRO:Usergroup', $this->in->getUint('usergroup_id'));
        }

        foreach ($feedback as $feedback) {
            switch ($action) {
                case 'add-to-organization':
                    if ($organization) {
                        foreach ($people as $p) {
                            $p->organization = $organization;
                            $this->em->persist($p);
                        }
                    }
                    break;

                case 'del-from-organization':
                    foreach ($people as $p) {
                        if ($p->organization) {
                            $p->organization = null;
                            $this->em->persist($p);
                        }
                    }
                    break;

                case 'add-to-usergroup':
                    if ($usergroup) {
                        foreach ($people as $p) {
                            if (!isset($p->usergroups[$usergroup->id])) {
                                $p->usergroups->add($usergroup);
                                $this->em->persist($p);
                            }
                        }
                    }
                    break;

                case 'del-form-usergroup':
                    if ($usergroup) {
                        foreach ($people as $p) {
                            if (isset($p->usergroups[$usergroup->id])) {
                                $p->usergroups->remove($usergroup->id);
                                $this->em->persist($p);
                            }
                        }
                    }
                    break;
            }
        }

        $this->em->flush();
        $this->em->commit();

        return $this->createJsonResponse(array(
            'success' => 1
        ));
    }*/

    //###########################################################################
    // quick-find
    //###########################################################################

    public function quickFindAction()
    {
        return $this->render('AgentBundle:PeopleSearch:quick-find.html.twig');
    }

    public function quickFindSearchAction()
    {
        $term_rules = RuleBuilder::newTermsBuilder();
        $terms      = $term_rules->readForm($this->in->getCleanValueArray('terms', 'raw', 'discard'));

        $searcher = new PersonSearch();
        foreach ($terms as $term) {
            $searcher->addTerm($term['type'], $term['op'], $term['options']);
        }

        $results = $searcher->getMatches();

        $data = [];

        if (!$results) {
            $data['no_results'] = true;
        } else {
            $data['num_results'] = count($results);

            $helper = new PeopleResults($this);
            $helper->setPeopleIds($results);

            $people = $helper->getPeopleForPage(1, 100);

            $data['html'] = $this->renderView('AgentBundle:PeopleSearch:quick-find-results.html.twig', [
                'people' => $people,
                'page'   => 1,
            ]);
        }

        return $this->createJsonResponse($data);
    }

    //###########################################################################
    // /agent/people-search/quick-search            agent_peoplesearch_performquick
    //###########################################################################

    public function performQuickSearchAction(Request $request)
    {
        $q = $this->in->getString('q');
        if (!$q) {
            $q = $this->in->getString('term');
        }

        $limit      = $this->in->getUint('limit') ?: 250;
        $withAgents = $this->in->getBool('with_agents');
        $excludeOrg = $this->in->getUint('exclude_org');
        $peopleList = [];

        if (StringEmail::isValueValid($q)) {
            // if the string is an exact email, we can try and find the user in usersources just by email
            /** @var Person $person */
            $person = $this->container->getSystemService('UsersourceManager')->findPersonByEmail($q);
            if ($person && !isset($peopleList[$person->getId()])) {
                $peopleList[$person->getId()] = [
                    'id'         => $person->getId(),
                    'first_name' => $person->getFirstName(),
                    'last_name'  => $person->getLastName(),
                    'email'      => $person->getPrimaryEmailAddress(),
                ];
            }
        }
        if (!count($peopleList)) {
            if ($this->container->getSetting('elastica.enabled')) {
                try {
                    $elasticSearch = $this->container->get('deskpro.search_manager.elasticsearch');
                    $elasticSearch->setPersonContext($this->person);

                    list($results, $resultMeta, $peopleTop) = $elasticSearch->quickSearch($q, 'date_active', ['person']);

                    if (isset($results['person'])) {
                        $results = $results['person'];
                    } else {
                        $results = [];
                    }

                    $results = array_slice($results, 0, $limit);

                    $output = [];
                    foreach ($results as $p) {
                        /** @var Person $p */
                        if (!$withAgents && $p->is_agent) {
                            continue;
                        }
                        if ($excludeOrg && $p->organization && $p->organization->id == $excludeOrg) {
                            continue;
                        }

                        $output[] = [
                            'id'         => $p->id,
                            'first_name' => $p->first_name,
                            'last_name'  => $p->last_name,
                            'email'      => $p->getPrimaryEmailAddress(),
                        ];
                    }

                    $peopleList = $output;
                } catch (\Exception $e) {
                    SystemErrorHandler::logException($e);
                    /** @var PersonRepository $rep */
                    $rep        = $this->em->getRepository(Person::class);
                    $peopleList = $rep->quickSearch($q, $this->in->getBool('start_with'), $withAgents, $excludeOrg, $limit);
                }
            } else {
                /** @var PersonRepository $rep */
                $rep        = $this->em->getRepository(Person::class);
                $peopleList = $rep->quickSearch($q, $this->in->getBool('start_with'), $withAgents, $excludeOrg, $limit);
            }
        }

        $format = $this->in->getString('format');

        if ($format == 'json' or (!$format and $this->in->getBool('ajax'))) {
            $tpl = 'AgentBundle:PeopleSearch:search_results.json.jsonphp';
        } else {
            $tpl = 'AgentBundle:PeopleSearch:search_results.html.twig';
            if ($format == 'simplelist') {
                $tpl = 'AgentBundle:PeopleSearch:search-results-simplelist.html.twig';
            }
        }

        if ($request->query->get('ignore_banned')) {
            /** @var BanEmailRepository $banRepo */
            $banRepo = $this->em->getRepository(BanEmail::class);

            /** @var Person $person */
            foreach ($peopleList as $num => $person) {
                if ($banRepo->isEmailBanned($person['email'])) {
                    unset($peopleList[$num]);
                }
            }
        }

        return $this->render($tpl, [
            'people_list' => $peopleList,
        ]);
    }
}
