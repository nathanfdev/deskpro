import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api, repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { addToCollection, updateCollection, removeFromCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import Immutable from 'immutable';
import { allVoicemailRecordsSelector } from '../Selectors/voicemailRecords';

export const loadVoicemailRecords = createAction(
  'VOICE_LOAD_VOICEMAIL_RECORDS',
  () => (dispatch) => {
    const promise = repository('VoicemailRecord').loadAll('voice_phone_call, person');
    promise.success(({ data, linked }) => {
      if (data) {
        dispatch(addToCollection('VoicemailRecord', 'all', data));
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

export const markVoicemalRecordAsListened = createAction(
  'VOICE_MAKR_VOICEMAIL_RECORD_AS_LISTENED',
  id => (dispatch, getState) => {
    const promise = api.sendPut(`DP_API/voicemail_records/${id}/mark_listened`);
    promise.success(() => {
      const state = getState();
      const records = allVoicemailRecordsSelector(state);

      let record = records.get(id);
      if (!record) {
        return;
      }

      record = record.set('is_listened', true);
      dispatch(updateCollection('VoicemailRecord', Immutable.List([record]), 'merge'));
    });

    return promise;
  }
);

export const deleteVoicemailRecord = createAction(
  'VOICE_DELETE_VOICEMAIL_RECORD',
  id => (dispatch) => {
    const promise = repository('VoicemailRecord').remove(id);
    promise.success(() => {
      dispatch(removeFromCollection('VoicemailRecord', 'all', [id]));
    });

    return promise;
  }
);

export const createVoicemailTicket = createAction(
  'VOICE_CREATE_VOICEMAIL_TICKET',
  id => (dispatch) => {
    const promise = api.sendPut(`DP_API/voicemail_records/${id}/create_ticket`);
    promise.success(({ data }) => {
      dispatch(removeFromCollection('VoicemailRecord', 'all', [id]));
      window.DeskPRO_Window.runPageRoute(`ticket:/agent/tickets/${data.id}`);
    });

    return promise;
  }
);
