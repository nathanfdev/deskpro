import { createSelector } from 'reselect';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { onlineAgentsSelector } from '../../Agent/Selectors/agents';
import { outboundNumbersSelector } from './numbers';

export const voiceAgentsSelector = createSelector(
  agentsSelector,
  agents => agents.filter(agent => agent.getIn(['agent_data', 'is_voice_enabled']))
);

export const voiceParticipantsSelector = createSelector(
  voiceAgentsSelector,
  meSelector,
  (agents, me) => agents.filter(agent => agent !== me)
);

export const callsForwardingEnabledSelector = createSelector(
  meSelector,
  me => me.getIn(['agent_data', 'is_voice_enabled'])
  && me.getIn(['agent_data', 'agent_can_use_forwarding'])
  && me.getIn(['agent_data', 'can_use_forwarding'])
  && me.getIn(['agent_data', 'forwarding_number'])
);

export const callsEnabledSelector = createSelector(
  meSelector,
  callsForwardingEnabledSelector,
  me => me.getIn(['agent_data', 'is_voice_enabled']) && me.getIn(['agent_data', 'agent_calls_enabled'])
);

export const voiceOnlineAgentsSelector = createSelector(
  voiceAgentsSelector,
  onlineAgentsSelector,
  (voiceAgents, onlineAgentIds) => voiceAgents.filter(agent =>
    onlineAgentIds.contains(agent.get('id')) && agent.getIn(['agent_data', 'agent_calls_enabled'])
  )
);

export const outboundCallsEnabledSelector = createSelector(
  meSelector,
  outboundNumbersSelector,
  (me, outboundNumbers) =>
    me
    && outboundNumbers
    && me.getIn(['agent_data', 'is_voice_enabled'])
    && me.getIn(['agent_data', 'outbound_calls_enabled'])
    && outboundNumbers.size > 0
);
