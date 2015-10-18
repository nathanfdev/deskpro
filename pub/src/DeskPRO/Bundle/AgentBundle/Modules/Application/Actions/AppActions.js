import { createAction } from 'Ampliflux';

export const setActiveApp = createAction('APP_SET_ACTIVE_APP');
export const collapseNav = createAction('APP_COLLAPSE_NAV');
export const expandNav = createAction('APP_EXPAND_NAV');
export const expandSwitcher = createAction('APP_EXPAND_SWITCHER');
export const collapseSwitcher = createAction('APP_COLLAPSE_SWITCHER');
export const toggleView = createAction('APP_TOGGLE_VIEW');
export const setColumnMode = createAction('APP_SET_COLUMN_MODE');
export const setColumnDimensions = createAction('APP_SET_COLUMN_DIMENSIONS');
export const showWelcomePage = createAction('APP_SHOW_WELCOME_PAGE');
export const hideWelcomePage = createAction('APP_HIDE_WELCOME_PAGE');

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
