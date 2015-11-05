import { createSelector } from 'reselect';

const stateSelector = state => state.NewTickets.list;

export const listParamsSelector = createSelector(
  stateSelector,
  state => state.get('listParams')
);

export const elementsSelector = createSelector(
  stateSelector,
  state => state.get('elements')
);

export const selectedSelector = createSelector(
  stateSelector,
  state => state.get('selected')
);

export const selectedCountSelector = createSelector(
  selectedSelector,
  selected => selected.size
);

export const isDoneSelector = createSelector(
  stateSelector,
  state => state.getIn(['async', 'done'])
);
