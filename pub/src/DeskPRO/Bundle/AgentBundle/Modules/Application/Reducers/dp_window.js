import * as actions from '../Actions/AppActions';
import { createReducer } from 'Ampliflux';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const initialState = {
  isLoaded: false,
  activeAppId: 'tickets',
  collapseNav: false,
  expandedSwitcher: false,
  taskView: constants.VIEW_MODE_LIST
};

export default createReducer(initialState, {
  [actions.setIsLoaded]: state => {
    return state.set('isLoaded', true);
  },
  [actions.setActiveApp]: (state, payload) => {
    return state.merge({activeAppId: payload, expandedSwitcher: false});
  },
  [actions.collapseNav]: state => {
    return state.set('collapseNav', true);
  },
  [actions.expandNav]: state => {
    return state.set('collapseNav', false);
  },
  [actions.expandSwitcher]: state => {
    return state.set('expandedSwitcher', true);
  },
  [actions.collapseSwitcher]: state => {
    return state.set('expandedSwitcher', false);
  },
  [actions.toggleView]: (state, payload) => {
    return state.set('taskView', payload);
  }
});
