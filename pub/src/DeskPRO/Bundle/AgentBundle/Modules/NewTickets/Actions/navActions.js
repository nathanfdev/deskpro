import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import { editedFilterIdSelector } from '../Selectors/nav';
import { loadAllDepartments } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/departmentsActions';
import { loadAllAgents } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentsActions';
import { loadAllAgentTeams } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentTeamsActions';
import { loadAll } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/RecordStores/Actions/languagesActions';
import { loadPeople as rsLoadPeople, releasePeopleRequest as rsReleasePeopleRequest }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';
import { loadOrganizations as rsLoadOrganizations, releaseOrganizationsRequest as rsReleaseOrganizationsRequest }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/organizationsActions';


export const RECORD_STORE_REQUEST_ID = 'tickets_nav';

export const startFilterEditing = createAction('TICKETS_NAV_FILTER_EDITING_START');
export const closeFilterEditing = createAction('TICKETS_NAV_FILTER_EDITING_CLOSE');
export const applyFilterEditing = createAction(
  'TICKETS_NAV_FILTER_EDITING_APPLY',
  (groupBy) => (dispatch, getState) => {
    const id = editedFilterIdSelector(getState());
    dispatch(markFilterLoading(id));
    dispatch(closeFilterEditing());

    // @todo Update record store filter.grouped_by value on success

    DpApi.sendPut(`DP_API/ticket_filters/${id}`, {group_by: groupBy})
         .success(() => dispatch(loadFilterCount(id)));
  }
);

export const initialLoad = createAction(
  'TICKETS_NAV_INITIAL_LOAD',
  () => dispatch => {
    dispatch(loadFilterSetsCount());
    dispatch(loadFilterSets());
    dispatch(loadLabels());
    dispatch(loadStarsCount());
    dispatch(loadStars());

    dispatch(loadAllDepartments());
    dispatch(loadAllAgents());
    dispatch(loadAllAgentTeams());
    dispatch(loadAll());
  }
);

export const unload = createAction(
  'TICKETS_NAV_UNLOAD',
  () => dispatch => {
    dispatch(releasePeople());
    dispatch(releaseOrganizations());
  }
);

/**
 * Load all filter sets counts
 */
const loadFilterSetsCount = createAction(
  'TICKETS_NAV_LOAD_FILTER_SETS_COUNT',
  () => dispatch => new Promise(resolve => DpApi.sendGet('DP_API/ticket_filter_sets/all/counts').success(
    response => {
      resolve(response.data);
      loadPersonAndOrganizationIds(response.data, dispatch);
    }
  ))
);

/**
 * Load all filter sets together with filters
 */
const loadFilterSets = createAction(
  'TICKETS_NAV_LOAD_FILTER_SETS',
  () => new Promise(resolve =>
    DpApi.sendGet('DP_API/ticket_filter_sets?include=ticket_filter').success(response => resolve({
      filterSets: response.data,
      filters: Object.values(response.linked.ticket_filter)
    }))
  )
);

/**
 * Load count of a single filter
 */
const loadFilterCount = createAction(
  'TICKETS_NAV_LOAD_FILTER_COUNT',
  id => dispatch => new Promise(resolve =>
    DpApi.sendGet(`DP_API/ticket_filters/${id}/count`)
         .success(response => {
           resolve(response.data);
           loadPersonAndOrganizationIds([{nested: [response.data]}], dispatch);
         }
    )
  )
);

const markFilterLoading = createAction('TICKETS_NAV_MARK_FILTER_AS_LOADING');

const loadPeople = createAction(
  'TICKETS_NAV_LOAD_PEOPLE',
  ids => dispatch => dispatch(rsLoadPeople(RECORD_STORE_REQUEST_ID, ids))
);
const releasePeople = createAction(
  'TICKETS_NAV_RELEASE_PEOPLE',
  () => dispatch => dispatch(rsReleasePeopleRequest(RECORD_STORE_REQUEST_ID))
);

const loadOrganizations = createAction(
  'TICKETS_NAV_LOAD_ORGANIZATIONS',
  ids => dispatch => dispatch(rsLoadOrganizations(RECORD_STORE_REQUEST_ID, ids))
);
const releaseOrganizations = createAction(
  'TICKETS_NAV_RELEASE_ORGANIZATIONS',
  () => dispatch => dispatch(rsReleaseOrganizationsRequest(RECORD_STORE_REQUEST_ID))
);

const loadLabels = createAction(
  'TICKETS_NAV_LOAD_LABELS',
  () => new Promise(resolve =>
    DpApi.sendGet('DP_API/ticket_labels').success(response => resolve(response.data))
  )
);

const loadStarsCount = createAction(
  'TICKETS_NAV_LOAD_STARS_COUNT',
  () => new Promise(resolve =>
    DpApi.sendGet('DP_API/ticket_stars_count').success(response => resolve(response.data.nested))
  )
);
const loadStars = createAction(
  'TICKETS_NAV_LOAD_STARS',
  () => new Promise(resolve =>
    DpApi.sendGet('DP_API/ticket_stars').success(response => resolve(response.data))
  )
);

/**
 * Load Person and Organization entities used in filter sets count
 *
 * @param {array} filterSetsCount Filter sets count
 * @param {function} dispatch Redux dispatch
 * @returns {void}
 */
function loadPersonAndOrganizationIds(filterSetsCount, dispatch) {
  const personIds = [];
  const organizationIds = [];
  filterSetsCount.forEach(filterSetCount => {
    filterSetCount.nested.forEach(filterCount => {
      if (filterCount.nested && filterCount.nested.length) {
        const grouping = filterCount.nested[0].grouped_by;
        if (grouping === 'person') {
          filterCount.nested.forEach(count => personIds.push(count.group));
        } else if (grouping === 'organization') {
          filterCount.nested.forEach(count => organizationIds.push(count.group));
        }
      }
    });
  });
  if (personIds.length) dispatch(loadPeople(personIds));
  if (organizationIds.length) dispatch(loadOrganizations(organizationIds));
}