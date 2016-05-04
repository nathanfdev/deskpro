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

export const widgetSessionPersonSelector = createSelector(
  widgetSessionSelector,
  session => session.get('person')
);

export const widgetSessionIsLoginSelector = createSelector(
  widgetSessionPersonSelector,
  person => !!person
);

export const widgetSettingsWrapperSelector = createSelector(
  stateSelector,
  state => state.get('settings')
);

// Widget settings selectors
export const widgetSettingsSelector = createSelector(
  widgetSettingsWrapperSelector,
  wrapper => wrapper.get('global')
);

// Widget settings selectors
export const widgetBrandSettingsSelector = createSelector(
  widgetSettingsWrapperSelector,
  wrapper => wrapper.get('brand')
);

export const widgetChatSettingsSelector = createSelector(
  widgetSettingsSelector,
  settings => settings.get('chat')
);

// Widget settings selectors
export const widgetBrandSettingsChatSelector = createSelector(
  widgetBrandSettingsSelector,
  brandSettings => brandSettings.get('chat')
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

// Widget settings selectors
export const widgetBrandSettingsChatEnabledSelector = createSelector(
  widgetBrandSettingsChatSelector,
  brandChatSettings => brandChatSettings.get('enabled')
);
