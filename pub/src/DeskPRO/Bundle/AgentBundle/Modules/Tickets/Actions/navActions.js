import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import { editedFilterIdSelector } from '../Selectors/nav';
import { loadPeople as rsLoadPeople, releasePeopleRequest as rsReleasePeopleRequest }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';
import { loadOrganizations as rsLoadOrganizations, releaseOrganizationsRequest as rsReleaseOrganizationsRequest }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/organizationsActions';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import { filterSetGroupingsSettingsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Selectors/settings';
import { compileParams } from 'DeskPRO/Bundle/AgentBundle/Services/ApiHelpers';
import { updateFilterGrouping } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Actions/settingsActions';

// Constants -----------------------------------------------------------------------------------------------------------

export const RECORD_STORE_REQUEST_ID = 'tickets_nav';

// Private -------------------------------------------------------------------------------------------------------------

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

const markFilterLoading = createAction('TICKETS_NAV_MARK_FILTER_AS_LOADING');
const removeFilterNestedCounts = createAction('TICKET_NAV_REMOVE_FILTER_NESTED_COUNTS');

/**
 * Load Person and Organization entities used in filter sets count
 *
 * @param {array} filterSetsCount Filter sets count
 * @param {function} dispatch Redux dispatch
 * @returns {void}
 */
function loadRelatedPeopleAndOrganizations(filterSetsCount, dispatch) {
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

/**
 * Load count of a single filter
 */
const loadFilterCount = createAction(
  'TICKETS_NAV_LOAD_FILTER_COUNT',
  id => (dispatch, getState) => new Promise(resolve => {
    const groupBy = filterSetGroupingsSettingsSelector(getState()).get(String(id), '');
    DpApi
      .sendGet(`DP_API/ticket_filters/${id}/count?group_by=` + groupBy)
      .success(response => {
        resolve(response.data);
        loadRelatedPeopleAndOrganizations([{nested: [response.data]}], dispatch);
      });
  })
);

// Public --------------------------------------------------------------------------------------------------------------

export const startFilterEditing = createAction('TICKETS_NAV_FILTER_EDITING_START');
export const closeFilterEditing = createAction('TICKETS_NAV_FILTER_EDITING_CLOSE');
export const applyFilterEditing = createAction(
  'TICKETS_NAV_FILTER_EDITING_APPLY',
  (groupBy) => (dispatch, getState) => {
    const id = editedFilterIdSelector(getState());
    dispatch(closeFilterEditing());

    // remove nested counts or mark filter as reloading depending on if grouping is applied
    if (!groupBy) {
      dispatch(removeFilterNestedCounts(id));
    } else {
      dispatch(markFilterLoading(id));
    }

    DpApi.sendPut(`DP_API/ticket_filters/${id}`, {group_by: groupBy}).success(() => {
      dispatch(updateFilterGrouping(id, groupBy));

      // reload filter counts if grouping is applied
      if (groupBy) {
        dispatch(loadFilterCount(id));
      }
    });
  }
);
export const initialLoad = createAction(
  'TICKETS_NAV_INITIAL_LOAD',
  () => (dispatch, getState) => new Promise(
      resolve => {
        const groupingQueryString =
          compileParams({group_by: filterSetGroupingsSettingsSelector(getState()).toJS()}).replace(/&/g, '%26');

        const batch = 'DP_API/batch'
          + '?get[filterSetsCount]=DP_API/ticket_filter_sets/all/counts%3F' + groupingQueryString
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
          loadRelatedPeopleAndOrganizations(payload.filterSetsCount, dispatch);
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