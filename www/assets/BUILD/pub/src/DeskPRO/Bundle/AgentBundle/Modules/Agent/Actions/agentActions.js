import { createAction } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
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

export const editAgent = createAction(
  'AGENT_EDIT_AGENT',
  (id, data) => (dispatch) => {
    const person = Immutable.fromJS({ ...data, id });
    dispatch(updateCollection('Person', Immutable.List([person]), 'merge'));

    return repository('Person').update(data, id);
  }
);
