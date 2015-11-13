import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';
import { createSelector } from 'reselect';

const stateSelector = state => state.Tasks.list;

export const currentNavSelector = hashStateSelectorFactory(['nav', 'active']);
export const currentViewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');
export const currentSortSelector = hashStateSelectorFactory(['list', 'sort'], 'date_created');
export const currentOrderSelector = hashStateSelectorFactory(['list', 'order'], 'desc');
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export const listFilterSelector = createSelector(
  stateSelector,
  state => state.getIn(['listParams', 'filter'])
);

export const tableVisibleFieldsSelector = createSelector(
  stateSelector,
  state => state.getIn(['visibleFields', constants.VIEW_MODE_TABLE])
);

export const cardVisibleFieldsSelector = createSelector(
  stateSelector,
  state => state.getIn(['visibleFields', constants.VIEW_MODE_CARD])
);

export const kanbanVisibleFieldsSelector = createSelector(
  stateSelector,
  state => state.getIn(['visibleFields', constants.VIEW_MODE_KANBAN])
);

export const calendarVisibleFieldsSelector = createSelector(
  stateSelector,
  state => state.getIn(['visibleFields', constants.VIEW_MODE_CALENDAR])
);
