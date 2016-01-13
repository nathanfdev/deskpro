import { createSelector } from 'reselect';

const stateSelector = state => state.Application.dpWindow;

export const showWelcomePageSelector = createSelector(
  stateSelector,
  state => state.get('showWelcomePage')
);

export const coverShownSelector = createSelector(
  stateSelector,
  state => state.get('coverShown')
);

export const currentAppSelector = createSelector(
  stateSelector,
  state => state.get('activeAppId')
);
