import {createAction} from "Ampliflux/actions";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

export const changeTab = createAction("TICKETS_CHANGE_TAB");
export const selectTicketFilter = createAction("TICKETS_SELECT_FILTER");

export const loadFilterSets = createAction(
  "TICKETS_LOAD_FILTER_SETS",
  (trigger) => {
    DpApi.sendGet('DP_API/ticket_filter_sets').then(
      (values) => trigger(values.getData())
    );
  }
);

export const loadFilterTickets = createAction(
  "TICKETS_LOAD_TICKETS",
  (trigger, filter_id) => {
    DpApi.sendGet('DP_API/ticket_filters/' + filter_id + '/tickets').then(
      values => trigger(values.getData())
    );
  }
);

export const loadFilterSetsCounts = createAction(
  "TICKETS_LOAD_FILTER_COUNTS",
  (trigger) => {
    DpApi.sendGet('DP_API/ticket_filter_sets/all/counts').then(
      values => trigger(values.getData())
    );
  }
);

export const loadFiltersInSet = createAction(
  "TICKETS_LOAD_FILTER_SET_FILTERS",
  (trigger, filter_set_id) => {
    DpApi.sendGet('DP_API/ticket_filter_sets/' + filter_set_id + '/filters').then(
      values => trigger({
        filter_set_id: filter_set_id,
        filters: values.getData().data
      })
    );
  }
);

export const loadFilterGroups = createAction(
  "TICKETS_LOAD_FILTER_GROUPS",
  (trigger, filter_id, grouping) => {
    DpApi.sendGet('DP_API/ticket_filters/' + filter_id + '/count?group_by=' + grouping).then(
      values => trigger({
        filter_id: filter_id,
        grouping: grouping,
        data: values.getData().data
      })
    );
  }
);
