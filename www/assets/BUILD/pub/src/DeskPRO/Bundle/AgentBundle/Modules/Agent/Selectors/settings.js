import { createSelector } from 'reselect';

const stateSelector = state => state.Agent.settings;
const ticketsSettingsSelector = createSelector(stateSelector, state => state.get('tickets'));
export const filterSetGroupingsSettingsSelector = createSelector(
  ticketsSettingsSelector,
  state => state.get('filter_groupings')
);
