import { createSelector } from 'reselect';

const stateSelector = state => state.Agent.info;
const ticketsInfoSelector = createSelector(stateSelector, state => state.get('tickets'));
export const filterGroupFieldsSettingsSelector = createSelector(
  ticketsInfoSelector,
  state => state.get('group_fields')
);
