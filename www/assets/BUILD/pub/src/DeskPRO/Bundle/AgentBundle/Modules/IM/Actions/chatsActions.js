import { createAction } from 'Ampliflux';
import { api, repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { addToCollection, removeFromCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const toggleOverlay = createAction('IM_TOGGLE_OVERLAY');

export const openChat = createAction('IM_OPEN_CHAT');

export const toggleGroupDrawer = createAction('IM_TOGGLE_GROUP_ADD_DRAWER');

export const markChatAsManuallyClosed = createAction(
  'MARK_CHAT_AS_CLOSED',
  chatId => chatId
);

export const markChatAsStartedByMe = createAction(
  'MARK_CHAT_AS_STARTED_BY_ME',
  chatId => chatId
);

export const closeChat = createAction(
  'IM_CLOSE_CHAT',
  (chatId = null) => (dispatch) => {
    if (chatId) {
      dispatch(markChatAsManuallyClosed(chatId));
    }
    return chatId;
  }
);

export const openGroupDrawer = createAction(
  'IM_OPEN_GROUP_ADD_DRAWER',
  (agentIds, editChat) => (dispatch) => {
    dispatch(closeChat());
    return { agentIds, editChat };
  }
);

export const closeGroupDrawer = createAction('IM_CLOSE_GROUP_ADD_DRAWER');

export const hideChat = createAction(
  'IM_HIDE_CHAT',
  (chatId, hideTime) => (dispatch) => {
    dispatch(closeChat(chatId));
    const hiddenChats = localStorage.getItem('hiddenChats') ? JSON.parse(localStorage.getItem('hiddenChats')) : {};
    hiddenChats[chatId] = hideTime;
    localStorage.setItem('hiddenChats', JSON.stringify(hiddenChats));
    return hiddenChats;
  }
);

export const revealChat = createAction(
  'IM_REVEAL_CHAT',
  (chatId) => {
    const hiddenChats = localStorage.getItem('hiddenChats') ? JSON.parse(localStorage.getItem('hiddenChats')) : {};
    if (hiddenChats[chatId]) {
      delete hiddenChats[chatId];
      localStorage.setItem('hiddenChats', JSON.stringify(hiddenChats));
    }
    return hiddenChats;
  }
);

const processChat = (chatId, chat, dispatch) => {
  const records = {};
  records[chatId] = chat;
  dispatch(markChatAsStartedByMe(chatId));
  dispatch(addToCollection('AgentChat', 'recent', records, [parseInt(chatId, 10)]));
  dispatch(revealChat(chatId));
  dispatch(markChatAsManuallyClosed(chatId));

  return records;
};

export const startChat = createAction(
  'IM_START_CHAT',
  (targetParams, chatId = null, forced = false) => (dispatch, getState) => {
    dispatch(closeGroupDrawer);
    if (!(forced && getState().IM.chats.getIn(['manuallyClosed', chatId]))) {
      dispatch(openChat());
    }
    return new Promise(
      (resolve, reject) => {
        const store = getState().RecordsStore.store.get('AgentChat');
        const chat = store.get('records').toJS()[chatId];
        if (chatId && chat) {
          processChat(chatId, chat, dispatch);
          return resolve(store.get('records').toJS()[chatId]);
        }

        let method;
        if (chatId) {
          method = () => repository('AgentChat').load(chatId);
        } else {
          method = () => repository('AgentChat').startChat(targetParams.id, targetParams.type, targetParams.name || '');
        }
        return method()
          .success((response) => {
            const records = processChat(response.data.id, response.data, dispatch);
            if (response.data.chat_type === 'group') {
              dispatch(addToCollection('AgentChat', 'group', records, [parseInt(response.data.id, 10)]));
            }
            return resolve(response.data);
          })
          .error(response => reject(response));
      }
    );
  }
);

export const updateChat = createAction(
  'IM_UPDATE_CHAT',
  (chatId, ids, name) => (dispatch) => {
    repository('AgentChat').updateChat(chatId, ids, name).then(() => repository('AgentChat').load(chatId).success((response) => {
      const records = {};
      records[response.data.id] = response.data;
      dispatch(removeFromCollection('AgentChat', 'recent', [parseInt(response.data.id, 10)]));
      dispatch(addToCollection('AgentChat', 'recent', records, [parseInt(response.data.id, 10)]));
      dispatch(startChat(null, chatId));
    }));
  }
);

export const loadActiveTabs = createAction(
  'IM_LOAD_ACTIVE_TABS',
  () => DeskPRO_Window.TabBar.getTabs() // eslint-disable-line no-undef
);

export const countSlice = () => {
  const slices = Math.floor((window.innerWidth - 890) / 30);
  if (slices < 1) {
    return 1;
  } else if (slices > 15) {
    return 15;
  }
  return slices;
};

export const loadRecentChats = createAction(
  'IM_LOAD_RECENT_CHATS',
  () => (dispatch) => {
    const slice = countSlice();
    api
      .sendGet(`DP_API/agent_chats?order_by=date_last_message&order_dir=desc&count=${slice}&include=person`)
      .success((response) => {
        dispatch(addToCollection('Person', 'people', response.linked.person));
        dispatch(addToCollection('AgentChat', 'recent', response.data));
      });
    return {};
  }
);

export const loadGroups = createAction(
  'IM_LOAD_RECENT_CHATS',
  () => (dispatch) => {
    api.sendGet('DP_API/agent_chats/groups?include=person').success((response) => {
      dispatch(addToCollection('Person', 'people', response.linked.person));
      dispatch(addToCollection('AgentChat', 'group', response.data));
    });
    return {};
  }
);

export const deleteGroup = createAction(
  'IM_DELETE_GROUP',
  chatId => (dispatch) => {
    repository('AgentChat').deleteGroup(chatId).then(() => {
      dispatch(removeFromCollection('AgentChat', 'recent', [chatId]));
      dispatch(removeFromCollection('AgentChat', 'group', [chatId]));
    });
  }
);

export const leaveGroup = createAction(
  'IM_DELETE_GROUP',
  chatId => (dispatch) => {
    repository('AgentChat').leaveGroup(chatId).then(() => {
      dispatch(removeFromCollection('AgentChat', 'recent', [chatId]));
      dispatch(removeFromCollection('AgentChat', 'group', [chatId]));
    });
  }
);
