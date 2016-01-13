import { createSelector } from 'reselect';

const stateSelector = state => state.Application.massActions;

export const selectedSelector = createSelector(
  stateSelector,
    state => state.get('selected')
);

