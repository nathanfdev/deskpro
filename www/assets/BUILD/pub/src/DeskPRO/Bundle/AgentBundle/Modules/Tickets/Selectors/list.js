import { createSelector } from 'reselect';
import { hashStateSelectorFactory } from '../../../Modules/Application/Selectors/routing';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const stateSelector = state => state.Tickets.list;

export const elementsSelector = createSelector(
  stateSelector,
  state => state.get('elements')
);

export const currentViewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');

export const listParamsSelector = createSelector(
  stateSelector,
  state => state.get('listParams')
);

export const fieldsSelector = createSelector(
  stateSelector,
  state => state.get('fields')
);

export const tableFieldsSelector = createSelector(
  stateSelector,
  state => state.getIn(['fields', constants.VIEW_MODE_TABLE])
);

export const cardFieldsSelector = createSelector(
  stateSelector,
  state => state.getIn(['fields', constants.VIEW_MODE_CARD])
);

export const listOrderBySelector = createSelector(
  listParamsSelector,
  params => params.get('order_by')
);

export const listOrderDirSelector = createSelector(
  listParamsSelector,
  params => params.get('order_dir')
);

export const paginationSelector = createSelector(
  stateSelector,
  state => state.get('pagination')
);
