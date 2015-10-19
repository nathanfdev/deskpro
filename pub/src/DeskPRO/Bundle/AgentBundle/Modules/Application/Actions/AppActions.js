import { createAction } from 'Ampliflux';

export const setActiveApp = createAction('APP_SET_ACTIVE_APP');
export const expandSwitcher = createAction('APP_EXPAND_SWITCHER');
export const collapseSwitcher = createAction('APP_COLLAPSE_SWITCHER');
export const toggleView = createAction('APP_TOGGLE_VIEW');

// Welcome page actions
export const showWelcomePage = createAction('APP_SHOW_WELCOME_PAGE');
export const hideWelcomePage = createAction('APP_HIDE_WELCOME_PAGE');

// Workspace actions
export const toggleWorkspace = createAction('APP_TOGGLE_WORKSPACE');
export const closeWorkspace = createAction('APP_CLOSE_WORKSPACE');
export const setColumnMode = createAction('APP_SET_COLUMN_MODE');
export const collapseNav = createAction('APP_COLLAPSE_NAV');
export const expandNav = createAction('APP_EXPAND_NAV');
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

// Preferences actions
export const togglePreferences = createAction('APP_TOGGLE_PREFERENCES');
export const closePreferences = createAction('APP_CLOSE_PREFERENCES');
