import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';
import { reduceMapToProperty } from 'DeskPRO/Component/Util/Map';

function agentTeamsStateSel(state) {
  return state.RecordStores.agentTeams;
}

export const agentTeamsStateSelector = createStoreSelectors(agentTeamsStateSel);
export const createAgentTeamsRequestSelectors = createRequestSelectorsBuilder(agentTeamsStateSelector);

export const agentTeamsSelector = createSelector(
  createAgentTeamsRequestSelectors('all').recordsSel,
  teams => teams
);

export const agentTeamNamesSelector = createSelector(
  agentTeamsSelector,
  teams => reduceMapToProperty('name', teams)
);
