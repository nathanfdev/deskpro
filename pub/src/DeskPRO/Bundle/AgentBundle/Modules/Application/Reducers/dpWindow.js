import * as actions from '../Actions/appActions';
import * as bootstrapActions from '../Actions/bootstrapActions';
import { createReducer } from 'Ampliflux';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { setFullPayload, setValue } from 'Ampliflux/reducers/handlers';
import { async } from 'Ampliflux/reducers/handlers';
import jQuery from 'jquery';

const initialState = {
  activeAppId: 'tickets',
  collapseNav: localStorage.getItem('dpWindow.sidebarMode') === 'hover',
  taskView: constants.VIEW_MODE_CARD,
  columnMode: localStorage.getItem('dpWindow.columnMode') || 'column',
  columnDimensions: parseInt(localStorage.getItem('dpWindow.columnDimensions'), 10) || 40,
  sidebarMode: localStorage.getItem('dpWindow.sidebarMode') || 'static',
  winDims: { width: 800, height: 600 },
  showWelcomePage: true,
  isWorkspaceOpen: false,
  isPreferencesOpen: false,
  preferenceTab: 'profile',
  coverShown: false,
  isDoneInitialLoad: false,
  isPreloading: false
};

/**
 * Trigger a custom jquery event to handle List column width recalculation
 *
 * @return {void}
 */
function triggerDpLayoutResize() {
  setTimeout(() => jQuery(document).trigger('dpLayoutResize'), 25);
}

export default createReducer(initialState, {
  [actions.windowResize]: (state, payload) => {
    return state.merge({
      winDims: { width: payload.width || 800, height: payload.height || 600 }
    });
  },
  [actions.setActiveApp]: (state, payload) => {
    return state.merge({
      activeAppId: payload
    });
  },
  [actions.collapseNav]: state => {
    triggerDpLayoutResize();
    return state.set('collapseNav', true);
  },
  [actions.expandNav]: state => {
    triggerDpLayoutResize();
    return state.set('collapseNav', false);
  },
  [actions.toggleView]: setFullPayload('taskView'),
  [actions.toggleWorkspace]: state => {
    return state.merge({
      isWorkspaceOpen: !state.get('isWorkspaceOpen'),
      isPreferencesOpen: false
    });
  },
  [actions.closeWorkspace]: setValue('isWorkspaceOpen', false),
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
  },
  [actions.showWelcomePage]: setValue('showWelcomePage', true),
  [actions.doneInitialLoad]: state => state.merge({ showWelcomePage: false, isDoneInitialLoad: true }),
  [actions.togglePreferences]: state => {
    const isOpen = !state.get('isPreferencesOpen');

    return state.merge({
      isWorkspaceOpen: false,
      isPreferencesOpen: isOpen,
      coverShown: isOpen
    });
  },
  [actions.closePreferences]: state => {
    return state.merge({
      isPreferencesOpen: false,
      coverShown: false
    });
  },
  [actions.changePreferenceTab]: setFullPayload('preferenceTab'),
  [bootstrapActions.preloadData]: async({
    start: state => state.set('isPreloading', true),
    done: state => state.set('isPreloading', false)
  })
});
