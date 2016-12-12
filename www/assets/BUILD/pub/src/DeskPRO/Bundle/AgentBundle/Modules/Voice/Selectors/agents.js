import { createSelector } from 'reselect';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';

export const voiceAgentsSelector = createSelector(
  agentsSelector,
  agents => agents.filter(agent => agent.getIn(['agent_data', 'is_voice_enabled']))
);

export const voiceParticipantsSelector = createSelector(
  voiceAgentsSelector,
  meSelector,
  (agents, me) => agents.filter(agent => agent !== me)
);

export const callsEnabledSelector = createSelector(
  meSelector,
  me => me.getIn(['agent_data', 'is_voice_enabled']) && me.getIn(['agent_data', 'agent_calls_enabled'])
);
