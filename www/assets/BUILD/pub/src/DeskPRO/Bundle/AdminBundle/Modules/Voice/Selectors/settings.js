import { createSelector } from 'reselect';

const stateSelector = state => state.Voice.settings;

export const settingsSelector = createSelector(
  stateSelector,
  state => state.get('settings')
);

export const settingsLoadedSelector = createSelector(
  stateSelector,
  state => state.get('settingsLoaded')
);
