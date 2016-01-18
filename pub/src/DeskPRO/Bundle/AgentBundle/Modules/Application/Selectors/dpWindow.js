import { createSelector } from 'reselect';

const stateSelector = state => state.Application.dpWindow;
const globalStateSelector = state => state;

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

export const currentAppStateSelector = createSelector(
  globalStateSelector,
  (state) => {
    const currentApp = state.Application.dpWindow.get('activeAppId');
    return currentApp === 'crm' ?
      state.CRM // @ToDo remove this temp solution
      : state[currentApp.charAt(0).toUpperCase() + currentApp.slice(1)];
  }
);
