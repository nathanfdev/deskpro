import { createAction } from 'DeskPRO/Component/Ampliflux';
import { loadAll, addToCollection, updateCollection, removeFromCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { api, repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import Immutable from 'immutable';

export const loadQueues = createAction(
  'USER_CHAT_LOAD_QUEUES',
  () => dispatch => dispatch(loadAll('UserChatQueue'))
);

export const createQueue = createAction(
  'USER_CHAT_CREATE_QUEUE',
  data => dispatch => repository('UserChatQueue').create(data).success((response) => {
    dispatch(addToCollection('UserChatQueue', 'all', Immutable.List([Immutable.fromJS(response.data)])));
  })
);

export const updateQueue = createAction(
  'USER_CHAT_UPDATE_QUEUE',
  (id, data) => dispatch => repository('UserChatQueue').update(data, id).success(() => {
    dispatch(updateCollection('UserChatQueue', Immutable.List([Immutable.fromJS({ ...data, id })]), 'merge'));
  })
);

export const deleteQueue = createAction(
  'USER_CHAT_DELETE_QUEUE',
  id => dispatch => repository('UserChatQueue').remove(id).success(() => {
    dispatch(removeFromCollection('UserChatQueue', 'all', [id]));
  })
);

export const loadQueueSettings = createAction(
  'USER_CHAT_LOAD_QUEUE_SETTINGS',
  () => api.sendGet('DP_API/user_chat_queues/settings')
);

export const updateQueueSettings = createAction(
  'USER_CHAT_UPDATE_QUEUE_SETTINGS',
  data => api.sendPut('DP_API/user_chat_queues/settings', data)
);
