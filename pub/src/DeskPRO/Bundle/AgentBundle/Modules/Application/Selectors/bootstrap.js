import { createSelector } from 'reselect';

const stateSelector = state => state.Application.bootstrap;

export const showWelcomePageSelector = createSelector(
  stateSelector,
  state => state.get('showWelcomePage')
);

export const isPreloadingSelector = createSelector(
  stateSelector,
  state => state.get('isPreloading')
);

export const isBootstrappedSelector = createSelector(
  stateSelector,
  state => state.get('isDoneInitialLoad') && !state.get('isPreloading')
);