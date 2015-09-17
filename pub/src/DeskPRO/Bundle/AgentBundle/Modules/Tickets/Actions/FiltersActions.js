import {createAction} from "Ampliflux";
import * as FilterSets from "DeskPRO/Bundle/AgentBundle/Services/Api/FilterSets";
import * as Filters from "DeskPRO/Bundle/AgentBundle/Services/Api/Filters";

export const changeTab = createAction("TICKETS_CHANGE_TAB");
export const selectTicketFilter = createAction("TICKETS_SELECT_FILTER");

export const loadFilterSets = createAction(
  "TICKETS_LOAD_FILTER_SETS",
  (trigger) => FilterSets.loadAll().then(
    (values) => trigger(values.getData())
  )
);

export const loadFilterTickets = createAction(
  "TICKETS_LOAD_TICKETS",
  (trigger, filter_id) => Filters.loadTickets(filter_id).then(
      values => trigger(values.getData())
  )
);

export const loadFilterSetsCounts = createAction(
  "TICKETS_LOAD_FILTER_COUNTS",
  (trigger) => FilterSets.count().then(
      values => trigger(values.getData())
  )
);

export const loadFiltersInSet = createAction(
  "TICKETS_LOAD_FILTER_SET_FILTERS",
  (trigger, filter_set_id) => {
    Filters.loadFiltersInSet(filter_set_id).then(
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
    Filters.ticketCountsForFilter(filter_id, grouping).then(
      values => trigger({
        filter_id: filter_id,
        grouping: grouping,
        data: values.getData().data
      })
    );
  }
);
