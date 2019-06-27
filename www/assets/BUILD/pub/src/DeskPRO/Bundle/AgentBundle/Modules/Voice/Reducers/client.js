import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import { storageAvailable } from 'DeskPRO/Component/Util/storageAvailable';
import { setFullPayload, pushPayloadToCollection, deletePayloadFromCollection, setValue, composeHandlers } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/clientActions';

let ringingVolume = 100;
if (storageAvailable('localStorage') && localStorage.getItem('dpAgent.voice.ringingVolume') !== null) {
  ringingVolume = localStorage.getItem('dpAgent.voice.ringingVolume');
}

const initialState = {
  waitingConnection: false,
  micEnabled:        false,
  incomingCalls:     [],
  outgoingCall:      null,
  connections:       [],
  onlineAgents:      [],
  ringingVolume,
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
  [actions.addConnection]: composeHandlers(
    pushPayloadToCollection('connections'),
    setValue('waitingConnection', false)
  ),
  [actions.removeConnection]:  deletePayloadFromCollection('connections'),
  [actions.setOutgoingCall]:   setFullPayload('outgoingCall'),
  [actions.resetOutgoingCall]: setValue('outgoingCall', null),
  [actions.setRingingVolume]:  setFullPayload('ringingVolume'),
  [actions.waitingConnection]: setValue('waitingConnection', true),
  [actions.setOnlineAgents]:   setFullPayload('onlineAgents'),
  [actions.setAgentAsIdle]:    (state, agentId) => {
    const newOnlineStatus = state.get('onlineAgents').map((onlineStatus) => {
      if (onlineStatus.get('agent_id') === agentId) {
        return onlineStatus.set('busy_for_voice', false);
      }

      return onlineStatus;
    });

    return state.set('onlineAgents', newOnlineStatus);
  },
  [actions.setAgentAsBusy]: (state, agentId) => {
    const newOnlineStatus = state.get('onlineAgents').map((onlineStatus) => {
      if (onlineStatus.get('agent_id') === agentId) {
        return onlineStatus.set('busy_for_voice', true);
      }

      return onlineStatus;
    });

    return state.set('onlineAgents', newOnlineStatus);
  }
});
