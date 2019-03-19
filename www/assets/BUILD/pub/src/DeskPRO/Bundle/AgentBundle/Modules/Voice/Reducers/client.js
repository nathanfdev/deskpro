import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import { storageAvailable } from 'DeskPRO/Component/Util/storageAvailable';
import { setFullPayload, pushPayloadToCollection, deletePayloadFromCollection, setValue } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/clientActions';

let ringingVolume = 100;
if (storageAvailable('localStorage') && localStorage.getItem('dpAgent.voice.ringingVolume') !== null) {
  ringingVolume = localStorage.getItem('dpAgent.voice.ringingVolume');
}

const initialState = {
  micEnabled:    false,
  incomingCalls: [],
  outgoingCall:  null,
  connections:   [],
  ringingVolume
};

export default createReducer(initialState, {
  [actions.setMicEnabled]:     setFullPayload('micEnabled'),
  [actions.setVoiceSettings]:  setFullPayload('settings'),
  [actions.addIncomingCall]:   pushPayloadToCollection('incomingCalls'),
  [actions.updateIncomigCall]: (state, payload) => {
    let incomingCalls = state.get('incomingCalls');
    if (payload && payload.get('call_id')) {
      const existingCall = incomingCalls.filter(incomingCall => incomingCall.get('call_id') === payload.get('call_id')).first();
      if (existingCall) {
        incomingCalls = incomingCalls.set(incomingCalls.indexOf(existingCall), payload);
      }
    }

    return state.set('incomingCalls', incomingCalls);
  },
  [actions.removeIncomingCall]: (state, payload) => {
    let incomingCalls = state.get('incomingCalls');
    if (payload && payload.get('call_id')) {
      incomingCalls = incomingCalls.filter(incomingCall => incomingCall.get('call_id') !== payload.get('call_id'));
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
  [actions.addConnection]:     pushPayloadToCollection('connections'),
  [actions.removeConnection]:  deletePayloadFromCollection('connections'),
  [actions.setOutgoingCall]:   setFullPayload('outgoingCall'),
  [actions.resetOutgoingCall]: setValue('outgoingCall', null),
  [actions.setRingingVolume]:  setFullPayload('ringingVolume')
});
