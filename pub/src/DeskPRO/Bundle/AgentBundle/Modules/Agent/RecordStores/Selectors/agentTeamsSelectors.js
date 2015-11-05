import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';
import { reduceImmutableToProperty } from 'DeskPRO/Component/Util/Map';

function agentTeamsStateSel(state) {
  return state.RecordStores.Agent.agentTeams;
}

export const agentTeamsStateSelector = createStoreSelectors(agentTeamsStateSel);
export const createAgentTeamsRequestSelectors = createRequestSelectorsBuilder(agentTeamsStateSelector);

export const agentTeamsSelector = createSelector(
  createAgentTeamsRequestSelectors('all').recordsSel,
  teams => teams
);

export const myAgentTeamsSelector = createSelector(
  createAgentTeamsRequestSelectors('my').recordsSel,
  teams => teams
);

export const myAgentTeamsStatusSelector = createSelector(
  createAgentTeamsRequestSelectors('my').statusSel,
  teams => teams
);

export const agentTeamNamesSelector = createSelector(
  agentTeamsSelector,
  teams => reduceImmutableToProperty('name', teams)
);
