import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { addToCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

const setIncludes = (promise, dispatch) => {
  promise.success(({ linked }) => {
    if (linked.person) {
      dispatch(addToCollection('Person', 'all', Object.values(linked.person)));
    }
    if (linked.ticket) {
      dispatch(addToCollection('Ticket', 'all', Object.values(linked.ticket)));
    }
  });
};

export const loadPhoneCalls = createAction(
  'ADMIN_VOICE_LOAD_PHONE_CALLS',
  page => (dispatch) => {
    const promise = repository('VoicePhoneCall').search({ page }, 'ticket, person');
    setIncludes(promise, dispatch);

    return promise;
  }
);

export const loadPhoneCall = createAction(
  'ADMIN_VOICE_LOAD_PHONE_CALL',
  id => (dispatch) => {
    const promise = repository('VoicePhoneCall').load(id, 'ticket, person');
    setIncludes(promise, dispatch);

    return promise;
  }
);

export const openDialpad = createAction(
  'ADMIN_VOICE_OPEN_DIALPAD',
  number => window.parent.AgentLegacyBundle.openVoiceDialpad(number)
);
