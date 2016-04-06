import { createSelector } from 'reselect';

const stateSelector = state => state.Application.bootstrap;

export const widgetLoadedSelector = createSelector(
  stateSelector,
  state => state.get('loaded')
);

// Widget session selectors
export const widgetSessionSelector = createSelector(
  stateSelector,
  state => state.get('session')
);

export const widgetSessionCodeSelector = createSelector(
  widgetSessionSelector,
  session => session.get('session_code')
);

export const widgetSessionIsLoginSelector = createSelector(
  widgetSessionSelector,
  session => session.get('is_login')
);

// Widget settings selectors
export const widgetSettingsSelector = createSelector(
  stateSelector,
  state => state.get('settings')
);

export const widgetChatSettingsSelector = createSelector(
  widgetSettingsSelector,
  settings => settings.get('chat')
);

export const requireChatEmailValidationSelector = createSelector(
  widgetChatSettingsSelector,
  settings => settings.get('email_validation')
);

export const requireChatLoginSelector = createSelector(
  widgetChatSettingsSelector,
  settings => settings.get('require_login')
);

// Company options selectors
export const companyOptionsSelector = createSelector(
  widgetSettingsSelector,
  settings => settings.get('company')
);

export const companyNameSelector = createSelector(
  companyOptionsSelector,
  settings => settings.get('name')
);

export const companyLogoSelector = createSelector(
  companyOptionsSelector,
  settings => settings.get('logo')
);
