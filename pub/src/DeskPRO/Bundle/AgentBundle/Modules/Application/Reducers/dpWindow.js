import * as actions from '../Actions/AppActions';
import { createReducer } from 'Ampliflux';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import jQuery from 'jquery';

const initialState = {
  isLoaded: false,
  activeAppId: 'tickets',
  collapseNav: localStorage.getItem('dpWindow.sidebarMode') === 'hover',
  expandedSwitcher: false,
  taskView: constants.VIEW_MODE_CARD,
  columnMode: localStorage.getItem('dpWindow.columnMode') || 'column',
  columnDimensions: parseInt(localStorage.getItem('dpWindow.columnDimensions'), 10) || 40,
  sidebarMode: localStorage.getItem('dpWindow.sidebarMode') || 'static'
};

/**
 * Trigger a custom jquery event to handle List column width recalculation
 *
 * @return {void}
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
  [actions.setColumnMode]: (state, payload) => {
    localStorage.setItem('dpWindow.columnMode', payload);
    return state.set('columnMode', payload);
  },
  [actions.setColumnDimensions]: (state, payload) => {
    triggerDpLayoutResize();
    localStorage.setItem('dpWindow.columnDimensions', payload);
    return state.set('columnDimensions', payload || 0);
  },
  [actions.setSidebarMode]: (state, payload) => {
    localStorage.setItem('dpWindow.sidebarMode', payload);
    return state.set('sidebarMode', payload);
  }
});
