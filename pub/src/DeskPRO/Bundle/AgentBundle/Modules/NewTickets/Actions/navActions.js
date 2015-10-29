import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import { editedFilterIdSelector } from '../Selectors/nav';

export const loadFilterSetsCount = createAction(
  'TICKETS_NAV_LOAD_FILTER_SETS_COUNT',
  () => new Promise(resolve => DpApi.sendGet('DP_API/ticket_filter_sets/all/counts').success(
      response => resolve(response.data)
  ))
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
  }
);
