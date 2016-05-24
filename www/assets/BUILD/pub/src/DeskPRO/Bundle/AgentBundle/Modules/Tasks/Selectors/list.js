import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';
import { createSelector } from 'reselect';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const stateSelector = state => state.Tasks.list;

export const currentNavSelector = hashStateSelectorFactory(['nav', 'active']);
export const currentViewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');
export const currentOrderBySelector = hashStateSelectorFactory(['list', 'order_by'], 'date_created');
export const currentOrderDirSelector = hashStateSelectorFactory(['list', 'order_dir'], 'desc');

export const listParamsNavSelector = createSelector(
  stateSelector,
  state => state.getIn(['listParams', 'nav'])
);

export const listParamsFiltersSelector = createSelector(
  stateSelector,
  state => state.getIn(['listParams', 'filters'])
);

export const tableFieldsSelector = createSelector(
  stateSelector,
  state => state.getIn(['fields', constants.VIEW_MODE_TABLE])
);

export const cardFieldsSelector = createSelector(
  stateSelector,
  state => state.getIn(['fields', constants.VIEW_MODE_CARD])
);

export const kanbanFieldsSelector = createSelector(
  stateSelector,
  state => state.getIn(['fields', constants.VIEW_MODE_KANBAN])
);

export const calendarFieldsSelector = createSelector(
  stateSelector,
  state => state.getIn(['fields', constants.VIEW_MODE_CALENDAR])
);

export const elementsSelector = createSelector(
  stateSelector,
  state => state.get('elements')
);

export const isLoadedSelector = createSelector(
  stateSelector,
  state => state.getIn(['async', 'done'])
);
