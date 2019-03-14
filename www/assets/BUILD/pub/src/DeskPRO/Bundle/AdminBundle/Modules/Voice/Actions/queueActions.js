import { createAction } from 'DeskPRO/Component/Ampliflux';
import { loadAll, addToCollection, updateCollection, removeFromCollection, releaseCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import Immutable from 'immutable';

export const loadQueues = createAction(
  'VOICE_LOAD_QUEUES',
  () => dispatch => dispatch(loadAll('VoiceQueue'))
);

export const createQueue = createAction(
  'VOICE_CREATE_QUEUE',
  data => dispatch => repository('VoiceQueue').create(data).success((response) => {
    dispatch(addToCollection('VoiceQueue', 'all', Immutable.List([Immutable.fromJS(response.data)])));
    dispatch(releaseCollection('VoiceAccount', 'all'));
    dispatch(releaseCollection('Person', 'agents'));
  })
);

export const updateQueue = createAction(
  'VOICE_UPDATE_QUEUE',
  (id, data) => dispatch => repository('VoiceQueue').update(data, id).success(() => {
    dispatch(updateCollection('VoiceQueue', Immutable.List([Immutable.fromJS({ ...data, id })]), 'merge'));
    dispatch(releaseCollection('VoiceAccount', 'all'));
    dispatch(releaseCollection('Person', 'agents'));
  })
);

export const deleteQueue = createAction(
  'VOICE_DELETE_QUEUE',
  id => dispatch => repository('VoiceQueue').remove(id).success(() => {
    dispatch(removeFromCollection('VoiceQueue', 'all', [id]));
    dispatch(releaseCollection('VoiceAutoAttendant', 'all'));
  })
);
