import { createAction } from "redux-actions";

import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import ActionTypes from "./ActionTypes";

export const changeTab = createAction(ActionTypes.TICKETS_CHANGE_TAB);
export const setLoadedFilterSets = createAction(ActionTypes.TICKETS_LOAD_FILTER_SETS);
export const selectTicketFilter = createAction(ActionTypes.TICKETS_SELECT_FILTER);
export const setLoadedTickets = createAction(ActionTypes.TICKETS_LOAD_TICKETS);
export const setLoadedFilterSetsCounts = createAction(ActionTypes.TICKETS_LOAD_FILTER_COUNTS);
export const setLoadedFilterSetFilters = createAction(ActionTypes.TICKETS_LOAD_FILTER_SET_FILTERS);

export const loadFilterSets = () => {
  return dispatch => {
    DpApi.sendGet('DP_API/ticket_filter_sets').then(
      (values) => {
        dispatch(setLoadedFilterSets(values.getData()));
      }
    );
  }
}

export const loadFilterTickets = (filter_id) => {
  return (dispatch) => {
    DpApi.sendGet('DP_API/ticket_filters/' + filter_id + '/tickets').then(
      values => {
        dispatch(setLoadedTickets(values.getData()));
      }
    );
  }
}

export const loadFilterSetsCounts = () => {
  return (dispatch) => {
    DpApi.sendGet('DP_API/ticket_filter_sets/all/counts').then(
      values => {
        dispatch(setLoadedFilterSetsCounts(values.getData()));
      }
    );
  }
}

export const loadFiltersInSet = (filter_set_id) => {
  return (dispatch) => {
    DpApi.sendGet('DP_API/ticket_filter_sets/' + filter_set_id + '/filters').then(
      values => {
        dispatch(setLoadedFilterSetFilters({
          filter_set_id: filter_set_id,
          filters: values.getData().data
        }));
      }
    );
  }
}
