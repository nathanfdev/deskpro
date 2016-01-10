import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';
import { createSelector } from 'reselect';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const stateSelector = state => state.Tasks.list;

export const currentNavSelector = hashStateSelectorFactory(['nav', 'active']);
export const currentViewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');
export const currentSortSelector = hashStateSelectorFactory(['list', 'sort'], 'date_created');
export const currentOrderSelector = hashStateSelectorFactory(['list', 'order'], 'desc');

export const listParamsNavSelector = createSelector(
  stateSelector,
  state => state.getIn(['listParams', 'nav'])
);

export const listParamsFiltersSelector = createSelector(
  stateSelector,
  state => state.getIn(['listParams', 'filters'])
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

export const selectedSelector = createSelector(
  stateSelector,
  state => state.get('selected')
);

export const selectedCountSelector = createSelector(
  selectedSelector,
  selected => selected.size
);

export const elementsSelector = createSelector(
  stateSelector,
  state => state.get('elements')
);

export const isDoneSelector = createSelector(
  stateSelector,
  state => state.getIn(['async', 'done'])
);
