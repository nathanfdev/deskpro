import { createSelector } from 'reselect';

const stateSelector = state => state.Login.login;

export const hasAuthSelector = createSelector(
  stateSelector,
  state => state.get('hasAuth')
);
