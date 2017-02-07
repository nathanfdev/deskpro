import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import { setFullPayload, pushPayloadToCollection, deletePayloadFromCollection, setValue } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/clientActions';

const initialState = {
  tokens:         {},
  activities:     {},
  incomingCalls:  [],
  connections:    [],
  outboundNumber: null
};

export default createReducer(initialState, {
  [actions.setVoiceTokens]:     setFullPayload('tokens'),
  [actions.setVoiceActivities]: setFullPayload('activities'),
  [actions.addIncomingCall]:    pushPayloadToCollection('incomingCalls'),
  [actions.updateIncomigCall]:  (state, payload) => {
    let incomingCalls = state.get('incomingCalls');
    if (payload.sid) {
      const existingCall = incomingCalls.filter(incomingCall => incomingCall.sid === payload.sid).first();
      if (existingCall) {
        incomingCalls = incomingCalls.set(incomingCalls.indexOf(existingCall), payload);
      }
    }

    return state.set('incomingCalls', incomingCalls);
  },
  [actions.removeIncomingCall]: (state, payload) => {
    let incomingCalls = state.get('incomingCalls');
    if (payload.sid) {
      incomingCalls = incomingCalls.filter(incomingCall => incomingCall.sid !== payload.sid);
    } else {
      incomingCalls = incomingCalls.filter(incomingCall =>
        !(incomingCall instanceof Immutable.Map)
        || incomingCall.get('call_id') !== payload.call_id
      );
    }

    return state.set('incomingCalls', incomingCalls);
  },
  [actions.removeConferenceIncomingCalls]: (state, conferenceSid) => {
    let incomingCalls = state.get('incomingCalls');
    incomingCalls = incomingCalls.filter(incomingCall =>
      !(incomingCall instanceof Immutable.Map)
      || incomingCall.get('conference_sid') !== conferenceSid
    );

    return state.set('incomingCalls', incomingCalls);
  },
  [actions.addConnection]:    pushPayloadToCollection('connections'),
  [actions.removeConnection]: deletePayloadFromCollection('connections'),
  [actions.openDialpad]:      setFullPayload('outboundNumber'),
  [actions.dialpadOpened]:    setValue('outboundNumber', null)
});
