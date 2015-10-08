import { createAction } from 'Ampliflux';
import * as PeopleApi from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';

export const setAppUser = createAction('APP_SET_USER');
export const setIsLoaded = createAction('APP_IS_LOADED');
export const setActiveApp = createAction('APP_SET_ACTIVE_APP');
export const routingStarted = createAction('APP_ROUTING_STARTED');
export const doTransitionTo = createAction('APP_TRANSITION_TO');
export const collapseNav = createAction('APP_COLLAPSE_NAV');
export const expandNav = createAction('APP_EXPAND_NAV');
export const expandSwitcher = createAction('APP_EXPAND_SWITCHER');
export const collapseSwitcher = createAction('APP_COLLAPSE_SWITCHER');
export const toggleView = createAction('APP_TOGGLE_VIEW');
export const setColumnMode = createAction('APP_SET_COLUMN_MODE');
export const setColumnDimensions = createAction('APP_SET_COLUMN_DIMENSIONS');

export const setSidebarMode = createAction(
  'APP_SET_SIDEBAR_MODE',
  mode => dispatch => {
    if (mode === 'static') {
      dispatch(expandNav());
    } else {
      dispatch(collapseNav());
    }

    return mode;
  }
);

export function transitionTo(pathname, query = null, state = null) {
  return dispatch => {
    dispatch(doTransitionTo([pathname, query, state]));
  };
}

export const loadWindow = createAction(
  'APP_LOAD_WINDOW',
  () => dispatch => PeopleApi.loadMe().then(promise => {
    const user = promise.getData().data;

    dispatch(setAppUser(user));
    dispatch(setIsLoaded());
  }
));
