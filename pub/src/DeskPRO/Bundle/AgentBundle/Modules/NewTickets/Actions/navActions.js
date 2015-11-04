import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import { editedFilterIdSelector } from '../Selectors/nav';
import { loadPeople as rsLoadPeople, releasePeopleRequest as rsReleasePeopleRequest }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';
import { loadOrganizations as rsLoadOrganizations, releaseOrganizationsRequest as rsReleaseOrganizationsRequest }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/organizationsActions';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';

// ---------------------------------------------------------------------------------------------------------------------
// Public

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
  () => dispatch => new Promise(
    resolve => {
      const batch = 'DP_API/batch'
        + '?get[filterSetsCount]=DP_API/ticket_filter_sets/all/counts'
        + '&get[filterSets]=DP_API/ticket_filter_sets'
        + '&get[filters]=DP_API/ticket_filters'
        + '&get[labels]=DP_API/ticket_labels'
        + '&get[starsCount]=DP_API/ticket_stars_count'
        + '&get[stars]=DP_API/ticket_stars'
      ;
      DpApi.sendGet(batch).success(({responses}) => {
        const payload = flattenBatchResponses(responses);
        payload.starsCount = payload.starsCount.nested;
        resolve(payload);
        loadPersonAndOrganizationIds(payload.filterSetsCount, dispatch);
      });
    }
  )
);

export const unload = createAction(
  'TICKETS_NAV_UNLOAD',
  () => dispatch => {
    dispatch(releasePeople());
    dispatch(releaseOrganizations());
  }
);

// ---------------------------------------------------------------------------------------------------------------------
// Private

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

const markFilterLoading = createAction(
  'TICKETS_NAV_MARK_FILTER_AS_LOADING'
);

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
