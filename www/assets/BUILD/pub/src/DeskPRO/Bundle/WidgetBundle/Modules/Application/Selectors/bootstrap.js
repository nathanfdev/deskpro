import { createSelector } from 'reselect';
import { storageAvailable } from 'DeskPRO/Component/Util/storageAvailable';

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

export const widgetSessionPersonSelector = createSelector(
  widgetSessionSelector,
  session => session.get('person')
);

export const widgetSessionChatIdSelector = createSelector(
  widgetSessionSelector,
  session => session.get('chat_id') || (storageAvailable('sessionStorage') && sessionStorage['dpWidget.chat.id'])
);

export const widgetSessionIsLoginSelector = createSelector(
  widgetSessionPersonSelector,
  person => !!person
);

// Widget settings selectors
export const widgetSettingsSelector = createSelector(
  stateSelector,
  state => state.get('settings')
);

// Chat options selectors
export const widgetChatSettingsSelector = createSelector(
  widgetSettingsSelector,
  settings => settings.get('chat')
);

export const widgetHasChatSelector = createSelector(
  widgetChatSettingsSelector,
  widgetSessionSelector,
  (chatSettings, sessionSettings) => chatSettings.get('enabled') && sessionSettings.get('is_chat_granted')
);

export const sessionLanguageSelector = createSelector(
  widgetSessionSelector,
  sessionSettings => sessionSettings.get('language')
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

export const buildNumSelector = createSelector(
  widgetSessionSelector,
  sessionSettings => sessionSettings.get('build_num')
);
