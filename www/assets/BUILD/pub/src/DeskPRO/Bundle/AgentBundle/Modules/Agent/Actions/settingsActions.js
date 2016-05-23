import { createAction } from 'Ampliflux';

export const setAgentSettings     = createAction('AGENT_SETTINGS_SET_SETTINGS');
export const updateFilterGrouping = createAction(
  'AGENT_SETTINGS_UPDATE_FILTER_GROUPING',
  (filterId, prefId, groupBy) => ({ filterId, prefId, groupBy }));
