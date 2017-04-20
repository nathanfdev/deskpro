import { createAction } from 'DeskPRO/Component/Ampliflux';
import { loadAll, updateCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import Immutable from 'immutable';
import { allQueuesSelector } from '../Selectors/queue';

export const loadQueues = createAction(
  'VOICE_LOAD_QUEUES',
  () => dispatch => dispatch(loadAll('VoiceQueue'))
);

export const updateQueue = createAction(
  'VOICE_UPDATE_QUEUE',
  (id, data) => (dispatch, getState) => api.sendPut(`DP_API/voice_queues/${id}/toggle_agent`, data).success(() => {
    const state = getState();
    const queues = allQueuesSelector(state);
    const me = meSelector(state);

    let queue = queues.get(id);
    if (!queue) {
      return;
    }

    const { enabled } = data;
    const agents = queue.get('agents');
    const contains = agents.contains(me.get('id'));

    if (!enabled && contains) {
      queue = queue.set('agents', agents.splice(agents.indexOf(me.get('id')), 1));
    } else if (enabled && !contains) {
      queue = queue.set('agents', agents.push(me.get('id')));
    }

    dispatch(updateCollection('VoiceQueue', Immutable.List([queue]), 'merge'));
  })
);
