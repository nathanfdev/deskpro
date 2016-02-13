import { createSelector } from 'reselect';
import Immutable from 'immutable';

const stateSelector = state => state.Application.dpWindow;
const globalStateSelector = state => state;

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
    const appState = currentApp === 'crm'
      ? state.CRM // @ToDo remove this temp solution
      : state[currentApp.charAt(0).toUpperCase() + currentApp.slice(1)];

    return appState ? appState : {list: Immutable.fromJS({})};
  }
);
