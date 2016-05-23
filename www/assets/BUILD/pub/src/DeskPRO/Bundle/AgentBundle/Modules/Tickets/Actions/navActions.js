import { createAction } from 'Ampliflux';
import { api, repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { editedFilterIdSelector } from '../Selectors/nav';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import { filterSetGroupingsSettingsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Selectors/settings';
import { updateFilterGrouping } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Actions/settingsActions';
import { setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { setAgentSettings } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Actions/settingsActions';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'tickets';

// Private -------------------------------------------------------------------------------------------------------------

const markFilterLoading        = createAction('TICKETS_NAV_MARK_FILTER_AS_LOADING');
const removeFilterNestedCounts = createAction('TICKET_NAV_REMOVE_FILTER_NESTED_COUNTS');

/**
 * Load count of a single filter
 */
const loadFilterCount = createAction(
  'TICKETS_NAV_LOAD_FILTER_COUNT',
  id => (dispatch, getState) => new Promise(resolve => {
    const groupBy = filterSetGroupingsSettingsSelector(getState()).get(String(id), '').get('main_grouping');
    repository('TicketFilter').loadFilterCounts(id, groupBy).success(response => resolve(response.data));
  })
);

// Public --------------------------------------------------------------------------------------------------------------

export const startFilterEditing = createAction('TICKETS_NAV_FILTER_EDITING_START');
export const closeFilterEditing = createAction('TICKETS_NAV_FILTER_EDITING_CLOSE');
export const applyFilterEditing = createAction(
  'TICKETS_NAV_FILTER_EDITING_APPLY',
  (groupBy, content, prefId) => (dispatch, getState) => {
    const id = editedFilterIdSelector(getState());
    dispatch(closeFilterEditing());

    // remove nested counts or mark filter as reloading depending on if grouping is applied
    if (!groupBy) {
      dispatch(removeFilterNestedCounts(id));
    } else {
      dispatch(markFilterLoading(id));
    }

    if (prefId) {
      repository('TicketFilter').putFilterPref(id, prefId, groupBy).success(() => {
        dispatch(updateFilterGrouping(id, prefId, groupBy));
        dispatch(loadFilterCount(id));
      });
    } else {
      repository('TicketFilter').postFilterPref(id, groupBy).success(() => {
        api.sendGet('DP_API/helpdesk/agent-client/settings').success(({ data }) => {
          dispatch(setAgentSettings(data));
          dispatch(loadFilterCount(id));
        });
      });
    }
  }
);

export const initialLoad = createAction(
  'TICKETS_NAV_INITIAL_LOAD',
  () => (dispatch, getState) => new Promise(
    (resolve) => {
      const currentGroupingParams = filterSetGroupingsSettingsSelector(getState()).toJS();
      const groupingQuery         = [];
      for (const key in currentGroupingParams) {
        if (currentGroupingParams.hasOwnProperty(key)) {
          groupingQuery.push(`group_by[${key}]` + '=' + currentGroupingParams[key].main_grouping);
        }
      }
      const groupingQueryString = groupingQuery.join('%26');

      const batch = 'DP_API/batch'
              + `?get[filterSetsCount]=DP_API/new/ticket_filter_sets/all/counts%3F${groupingQueryString}`
              + '&get[labels]=DP_API/ticket_labels'
              + '&get[categories]=DP_API/ticket_categories'
              + '&get[workflows]=DP_API/ticket_workflows'
              + '&get[products]=DP_API/ticket_products'
              + '&get[starsCount]=DP_API/ticket_stars/counts'
              + '&get[filters]=DP_API/new/ticket_filters'
        ;

      api.sendGet(batch).success(({ responses }) => {
        const payload      = flattenBatchResponses(responses);
        payload.starsCount = payload.starsCount.nested;
        dispatch(setCollection('TicketCategory', recordStoresId, payload.categories));
        dispatch(setCollection('TicketWorkflow', recordStoresId, payload.workflows));
        dispatch(setCollection('TicketProduct', recordStoresId, payload.products));
        delete payload.categories;
        delete payload.workflows;
        delete payload.products;
        resolve(payload);
      });
    }
  )
);
