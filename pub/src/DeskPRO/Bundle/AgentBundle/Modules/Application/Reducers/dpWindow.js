import * as actions from '../Actions/AppActions';
import { createReducer } from 'Ampliflux';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import jQuery from 'jquery';

const initialState = {
  isLoaded: false,
  activeAppId: 'tickets',
  collapseNav: false,
  expandedSwitcher: false,
  taskView: constants.VIEW_MODE_LIST,
  columnMode: 'column',
  columnDimensions: 30,
  sidebarMode: 'hover'
};

/**
 * Trigger a custom jquery event to handle List column width recalculation
 */
function triggerDpLayoutResize() {
  setTimeout(() => jQuery(document).trigger('dpLayoutResize'), 100);
}

export default createReducer(initialState, {
  [actions.setIsLoaded]: state => {
    return state.set('isLoaded', true);
  },
  [actions.setActiveApp]: (state, payload) => {
    return state.merge({activeAppId: payload, expandedSwitcher: false});
  },
  [actions.collapseNav]: state => {
    triggerDpLayoutResize();
    return state.set('collapseNav', true);
  },
  [actions.expandNav]: state => {
    triggerDpLayoutResize();
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
  },
  [actions.updateWorkspace]: (state, payload) => {
    return state.merge({
      columnMode: payload.columnMode,
      columnDimensions: payload.columnDimensions,
      sidebarMode: payload.sidebarMode
    });
  }
});
