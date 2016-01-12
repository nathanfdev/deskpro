import { createSelector } from 'reselect';

const stateSelector = state => state.Application.bootstrap;

export const widgetLoadedSelector = createSelector(
  stateSelector,
  state => state.get('loaded')
);

export const widgetSessionCodeSelector = createSelector(
  stateSelector,
  state => state.get('sessionCode')
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
