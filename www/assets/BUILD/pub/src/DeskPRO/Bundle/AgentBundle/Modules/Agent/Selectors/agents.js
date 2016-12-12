import { createSelector } from 'reselect';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';

const stateSelector = state => state.Agent.agents;

export const onlineAgentsSelector = createSelector(
  stateSelector,
  state => state.get('onlineAgents')
);

export const onlineUserChatAgentsSelector = createSelector(
  stateSelector,
  state => state.get('onlineUserChatAgents')
);

export const userChatEnabledSelector = createSelector(
  onlineUserChatAgentsSelector,
  meSelector,
  (userChatAgents, me) => userChatAgents.filter(id => id === me.get('id')).size > 0
);
