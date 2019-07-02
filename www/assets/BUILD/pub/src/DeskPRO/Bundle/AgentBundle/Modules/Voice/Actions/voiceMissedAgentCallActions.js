import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api, repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { addToCollection, updateCollection, removeFromCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import Immutable from 'immutable';
import { allVoiceMissedAgentCallsSelector } from '../Selectors/voicemailRecords';

export const loadVoiceMissedAgentCalls = createAction(
  'VOICE_LOAD_VOICE_MISSED_AGENT_CALLS',
  () => (dispatch) => {
    const promise = repository('VoiceMissedAgentCall').loadAll('voice_phone_call, person');
    promise.success(({ data, linked }) => {
      if (data) {
        dispatch(addToCollection('VoiceMissedAgentCall', 'all', data));
      }
      if (linked.voice_phone_call) {
        dispatch(addToCollection('VoicePhoneCall', 'all',  Object.values(linked.voice_phone_call)));
      }
      if (linked.person) {
        dispatch(addToCollection('Person', 'all',  Object.values(linked.person)));
      }
    });

    return promise;
  }
);

export const markVoiceMissedAgentCallAsListened = createAction(
  'VOICE_MAKR_VOICE_MISSED_AGENT_CALL_AS_LISTENED',
  id => (dispatch, getState) => {
    const promise = api.sendPut(`DP_API/voice_missed_agent_calls/${id}/mark_listened`);
    promise.success(() => {
      const state = getState();
      const records = allVoiceMissedAgentCallsSelector(state);

      let record = records.get(id);
      if (!record) {
        return;
      }

      record = record.set('is_listened', true);
      dispatch(updateCollection('VoiceMissedAgentCall', Immutable.List([record]), 'merge'));
    });

    return promise;
  }
);

export const deleteVoiceMissedAgentCall = createAction(
  'VOICE_DELETE_VOICE_MISSED_AGENT_CALL',
  id => (dispatch) => {
    const promise = repository('VoiceMissedAgentCall').remove(id);
    promise.success(() => {
      dispatch(removeFromCollection('VoiceMissedAgentCall', 'all', [id]));
    });

    return promise;
  }
);

export const createVoicemailTicket = createAction(
  'VOICE_CREATE_MISSED_AGENT_CALLL_TICKET',
  id => (dispatch) => {
    const promise = api.sendPut(`DP_API/voice_missed_agent_calls/${id}/create_ticket`);
    promise.success(({ data }) => {
      dispatch(removeFromCollection('VoiceMissedAgentCall', 'all', [id]));
      window.DeskPRO_Window.runPageRoute(`ticket:/agent/tickets/${data.id}`);
    });

    return promise;
  }
);
