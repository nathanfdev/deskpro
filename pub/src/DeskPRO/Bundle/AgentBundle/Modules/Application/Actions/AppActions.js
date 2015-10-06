import { createAction } from 'Ampliflux';
import * as PeopleApi from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';
import ActionTypes from './ActionTypes';

export const setAppUser = createAction(ActionTypes.APP_SET_USER);
export const setIsLoaded = createAction(ActionTypes.APP_IS_LOADED);
export const setActiveApp = createAction(ActionTypes.SET_ACTIVE_APP);
export const routingStarted = createAction(ActionTypes.ROUTING_STARTED);
export const doTransitionTo = createAction(ActionTypes.TRANSITION_TO);
export const collapseNav = createAction(ActionTypes.COLLAPSE_NAV);
export const expandNav = createAction(ActionTypes.EXPAND_NAV);
export const expandSwitcher = createAction(ActionTypes.EXPAND_SWITCHER);
export const collapseSwitcher = createAction(ActionTypes.COLLAPSE_SWITCHER);
export const toggleView = createAction(ActionTypes.TOGGLE_VIEW);
export const updateWorkspace = createAction(ActionTypes.APP_UPDATE_WORKSPACE);

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
