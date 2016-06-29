import { createSelector } from 'reselect';

const stateSelector = state => state.Application.bootstrap;

export const isBootstrappedSelector = createSelector(
  stateSelector,
  state => state.get('isBootstrapped')
);
