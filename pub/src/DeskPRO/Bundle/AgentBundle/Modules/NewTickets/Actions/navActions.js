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

export const loadFilterSetsCount = createAction(
  'TICKETS_NAV_LOAD_FILTER_SETS_COUNT',
  () => dispatch => new Promise(resolve => DpApi.sendGet('DP_API/ticket_filter_sets/all/counts').success(
    response => {
      resolve(response.data);

      // collect Person and Organization IDs used in counts to load needed entities
      const personIds = [];
      const organizationIds = [];
      response.data.forEach(filterSetCount => {
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
  ))
);

export const loadPeople = createAction(
  'TICKETS_NAV_LOAD_PEOPLE',
  ids => dispatch => dispatch(rsLoadPeople(RECORD_STORE_REQUEST_ID, ids))
);
export const releasePeople = createAction(
  'TICKETS_NAV_RELEASE_PEOPLE',
  () => dispatch => dispatch(rsReleasePeopleRequest(RECORD_STORE_REQUEST_ID))
);

export const loadOrganizations = createAction(
  'TICKETS_NAV_LOAD_ORGANIZATIONS',
  ids => dispatch => dispatch(rsLoadOrganizations(RECORD_STORE_REQUEST_ID, ids))
);
export const releaseOrganizations = createAction(
  'TICKETS_NAV_RELEASE_ORGANIZATIONS',
  () => dispatch => dispatch(rsReleaseOrganizationsRequest(RECORD_STORE_REQUEST_ID))
);

export const loadFilterSets = createAction(
  'TICKETS_NAV_LOAD_FILTER_SETS',
  () => new Promise(resolve => DpApi.sendGet('DP_API/ticket_filter_sets?include=ticket_filter').success(
    response => resolve({
      filterSets: response.data,
      filters: Object.values(response.linked.ticket_filter)
    })
  ))
);

export const loadLabels = createAction(
  'TICKETS_NAV_LOAD_LABELS',
  () => new Promise(resolve => DpApi.sendGet('DP_API/ticket_labels').success(response => resolve(response.data)))
);

export const loadStarsCount = createAction(
  'TICKETS_NAV_LOAD_STARS_COUNT',
  () => new Promise(resolve => DpApi.sendGet('DP_API/ticket_stars_count').success(response => resolve(response.data.nested)))
);
export const loadStars = createAction(
  'TICKETS_NAV_LOAD_STARS',
  () => new Promise(resolve => DpApi.sendGet('DP_API/ticket_stars').success(response => resolve(response.data)))
);

export const startFilterEditing = createAction('TICKETS_NAV_FILTER_EDITING_START');
export const closeFilterEditing = createAction('TICKETS_NAV_FILTER_EDITING_CLOSE');
export const applyFilterEditing = createAction(
  'TICKETS_NAV_FILTER_EDITING_APPLY',
  (groupBy) => (dispatch, getState) => {
    const id = editedFilterIdSelector(getState());
    dispatch(closeFilterEditing());
    DpApi.sendPut(`DP_API/ticket_filters/${id}`, {group_by: groupBy}).success(() => {
      dispatch(loadFilterSetsCount());
      dispatch(loadFilterSets());
    });
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
