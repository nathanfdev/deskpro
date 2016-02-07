import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';
import { editedFilterIdSelector } from '../Selectors/nav';
import { loadPeople as rsLoadPeople, releasePeopleRequest as rsReleasePeopleRequest }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';
import { loadOrganizations as rsLoadOrganizations, releaseOrganizationsRequest as rsReleaseOrganizationsRequest }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/organizationsActions';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import { filterSetGroupingsSettingsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Selectors/settings';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import { updateFilterGrouping } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Actions/settingsActions';

// Private -------------------------------------------------------------------------------------------------------------

const markFilterLoading = createAction('TICKETS_NAV_MARK_FILTER_AS_LOADING');
const removeFilterNestedCounts = createAction('TICKET_NAV_REMOVE_FILTER_NESTED_COUNTS');

/**
 * Load count of a single filter
 */
const loadFilterCount = createAction(
  'TICKETS_NAV_LOAD_FILTER_COUNT',
  id => (dispatch, getState) => new Promise(resolve => {
    const groupBy = filterSetGroupingsSettingsSelector(getState()).get(String(id), '');
    api
      .sendGet(`DP_API/ticket_filters/${id}/count?group_by=` + groupBy)
      .success(response => resolve(response.data));
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

    api.sendPut(`DP_API/ticket_filters/${id}`, {group_by: groupBy}).success(() => {
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
          + '&get[labels]=DP_API/ticket_labels'
          + '&get[starsCount]=DP_API/ticket_stars_counts'
          + '&get[filters]=DP_API/ticket_filters'
        ;

        api.sendGet(batch).success(({responses}) => {
          const payload = flattenBatchResponses(responses);
          payload.starsCount = payload.starsCount.nested;
          resolve(payload);
        });
      }
  )
);
