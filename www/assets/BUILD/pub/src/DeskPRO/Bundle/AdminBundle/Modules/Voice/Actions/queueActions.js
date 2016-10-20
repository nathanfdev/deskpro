import { createAction } from 'DeskPRO/Component/Ampliflux';
import { loadAll, addToCollection, updateCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
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
  })
);

export const updateQueue = createAction(
  'VOICE_UPDATE_QUEUE',
  (id, data) => dispatch => repository('VoiceQueue').update(data, id).success(() => {
    dispatch(updateCollection('VoiceQueue', Immutable.List([Immutable.fromJS({ ...data, id })]), 'merge'));
  })
);
