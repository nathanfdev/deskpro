import { createAction } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { updateCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';

export const setOnlineAgents = createAction(
  'AGENT_SET_ONLINE_AGENTS',
  (ids) => {
    const numericIds = [];
    ids.forEach(id => numericIds.push(parseInt(id, 10)));

    return numericIds;
  }
);

export const setOnlineUserChatAgents = createAction(
  'AGENT_SET_ONLINE_USER_CHAT_AGENTS',
  (ids) => {
    const numericIds = [];
    ids.forEach(id => numericIds.push(parseInt(id, 10)));

    return numericIds;
  }
);

export const updateAgentStatus = createAction('AGENT_UPDATE_STATUS');

export const toggleUserChat = createAction(
  'AGENT_TOGGLE_USER_CHAT',
  enabled => (dispatch, getState) => {
    const state = getState();
    const me = meSelector(state);

    window.$.ajax({
      url:  `${window.BASE_URL}agent/misc/set-agent-status/available`,
      type: 'POST',
      data: [{
        name:  'is_chat_available',
        value: enabled ? 1 : 0
      }]
    });

    return { me, enabled };
  }
);

export const editAgentProfile = createAction(
  'AGENT_EDIT_AGENT_PROFILE',
  data => (dispatch, getState) => {
    const state = getState();

    let me = meSelector(state);
    if (me) {
      const agentData = me.get('agent_data') ? me.get('agent_data').toJS() : {};
      me = me.set('agent_data', Immutable.fromJS({ ...agentData, ...data }));
      dispatch(updateCollection('Person', Immutable.List([me]), 'merge'));
    }

    return api.sendPut('DP_API/agents/profile', { agent_data: data });
  }
);
