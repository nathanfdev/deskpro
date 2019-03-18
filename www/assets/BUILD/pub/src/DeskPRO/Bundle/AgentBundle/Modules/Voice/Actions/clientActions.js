import { createAction } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import { storageAvailable } from 'DeskPRO/Component/Util/storageAvailable';
import { loadBatch, addToCollection, updateCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { connectionsSelector, incomingCallSelector, outgoingCallSelector } from '../Selectors/client';
import { allPhoneCallsSelector } from '../Selectors/phoneCalls';
import { allVoiceAccountsSelector } from '../Selectors/accounts';
import { allNumbersSelector } from '../Selectors/numbers';
import { closeIframes } from '../../Application/Actions/bootstrapActions';

export const setMicEnabled = createAction('VOICE_AGENT_SET_MIC_ENABLED');
export const setVoiceSettings = createAction('VOICE_AGENT_SET_SETTINGS');
export const addIncomingCall = createAction('VOICE_AGENT_ADD_RESERVATION');
export const updateIncomigCall = createAction('VOICE_AGENT_UPDATE_RESERVATION');
export const removeIncomingCall = createAction('VOICE_AGENT_REMOVE_RESERVATION');
export const removeConferenceIncomingCalls = createAction('VOICE_AGENT_REMOVE_CONFERENCE_RESERVATIONS');
export const addConnection = createAction('VOICE_AGENT_ADD_CONNECTION');
export const removeConnection = createAction('VOICE_AGENT_REMOVE_CONNECTION');
export const setOutgoingCall = createAction('VOICE_AGENT_deskpro_call_idSET_OUTGOING_CALL');
export const resetOutgoingCall = createAction('VOICE_AGENT_RESET_OUTGOING_CALL');
export const updateConnectionState = createAction('VOICE_AGENT_UPDATE_CONNECTION_STATE');

const filterConnection = (connection, callSid) => {
  if (!connection) {
    return false;
  }

  // filter for twilio
  if (connection.parameters && connection.parameters.CallSid === callSid) {
    return true;
  }

  // filter for plivo
  if (connection.getCallUUID && connection.getCallUUID() === callSid) {
    return true;
  }

  return false;
};

const hangupConnection = (connection) => {
  // twilio
  if (connection.disconnect) {
    connection.disconnect();
  }

  // plivo
  if (connection.hangup) {
    connection.hangup();
  }
};

// list of active voice clients
const clients = {};

export const setRingingVolume = createAction(
  'VOICE_AGENT_SET_RINGING_VOLUME',
  (value) => {
    if (storageAvailable('localStorage')) {
      localStorage.setItem('dpAgent.voice.ringingVolume', value);
    }

    return value;
  }
);

export const voiceBootstrap = createAction(
  'VOICE_AGENT_BOOTSTRAP',
  () => (dispatch, getState) => {
    const initialState = getState();
    const accounts = allVoiceAccountsSelector(initialState);

    if (!accounts.size) {
      return;
    }

    // check if mic is enabled
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
      navigator.mediaDevices.getUserMedia({ audio: true }).then((stream) => {
        // mark the mic as enabled
        stream.stop();
        dispatch(setMicEnabled(true));

        const messageBroker = window.DeskPRO_Window.getMessageBroker();
        messageBroker.addMessageListener('agent.voice.conference.incoming-call', (data) => {
          const personId = data.caller_person_id;
          if (personId) {
            dispatch(loadBatch('Person', personId, 'all'));
          }

          const relatedPeopleIds = data.related_people_ids;
          if (relatedPeopleIds) {
            dispatch(loadBatch('Person', relatedPeopleIds, 'all'));
          }

          dispatch(addIncomingCall(Immutable.fromJS(data)));
        });
        messageBroker.addMessageListener('agent.voice.conference.incoming-call-answered', (data) => {
          // another agent have already accepted the call
          // fetch the ticket info to get assigned agent
          const state = getState();
          let incomingCall = incomingCallSelector(state);
          if (incomingCall && data.call_id === incomingCall.get('call_id')) {
            incomingCall = incomingCall.set('assigned_agent', data.accepted_agent_id);
            dispatch(updateIncomigCall(incomingCall));

            // remove the reservation by timeout to show that another agent accepted the call
            setTimeout(() => dispatch(removeIncomingCall(incomingCall)), 5000);
          }
        });
        messageBroker.addMessageListener('agent.voice.conference.incoming-call-rejected', (data) => {
          // user canceled the call, remove notification
          const state = getState();
          const incomingCall = incomingCallSelector(state);

          if (incomingCall && data.call_id === incomingCall.get('call_id')) {
            dispatch(removeIncomingCall(incomingCall));
          }
        });
        messageBroker.addMessageListener('agent.voice.calls_enabled', (data) => {
          const state = getState();
          const agents = agentsSelector(state);

          let agent = agents.get(data.person_id);
          if (!agent) {
            return;
          }

          if (!agent.get('agent_data')) {
            agent = agent.set('agent_data', Immutable.fromJS({}));
          }

          agent = agent.setIn(['agent_data', 'agent_calls_enabled'], !!data.agent_calls_enabled);
          dispatch(updateCollection('Person', Immutable.List([agent]), 'replace'));
        });
        messageBroker.addMessageListener('agent.voice.conference.participant-invite', (data) => {
          if (data.caller_person_id) {
            dispatch(loadBatch('Person', data.caller_person_id, 'all'));
          }

          dispatch(addIncomingCall(Immutable.fromJS(data)));
        });
        messageBroker.addMessageListener('agent.voice.conference.participant-cancel', (data) => {
          dispatch(removeIncomingCall(Immutable.fromJS(data)));
        });
        messageBroker.addMessageListener('agent.voice.conference.status', (event) => {
          const eventName = event.StatusCallbackEvent;
          const phoneCall = Immutable.fromJS(event.phone_call);

          // realtime phone call updates
          const state = getState();
          const phoneCalls = allPhoneCallsSelector(state);

          if (phoneCalls.get(phoneCall.get('id'))) {
            dispatch(updateCollection('VoicePhoneCall', Immutable.List([phoneCall]), 'replace'));
          } else {
            dispatch(addToCollection('VoicePhoneCall', 'all', Immutable.List([phoneCall])));
          }

          // change worker status to idle on conference end
          if (eventName === 'conference-end') {
            dispatch(removeConferenceIncomingCalls(event.ConferenceSid));
          }

          dispatch(updateConnectionState({
            call_id: parseInt(event.phone_call.id, 10),
            state:   Immutable.fromJS({
              participants: event.agent_participants,
              hold:         !!event.hold
            })
          }));
        });
        messageBroker.addMessageListener('agent.voice.reached-voicemail', (data) => {
          dispatch(removeIncomingCall(Immutable.fromJS(data)));
        });
        messageBroker.addMessageListener('agent.voice.call-answered', (data) => {
          dispatch(removeIncomingCall(Immutable.fromJS(data)));
        });
        messageBroker.addMessageListener('agent.voice.call-ended', (data) => {
          dispatch(removeIncomingCall(Immutable.fromJS(data)));
        });
        messageBroker.addMessageListener('agent.voice.voicemail.new-message', (event) => {
          const state = getState();
          const me = meSelector(state);
          const data = event.data;

          if (data.data.agent !== me.get('id')) {
            return;
          }

          dispatch(addToCollection('VoicemailRecord', 'all', [data.data]));

          if (data.linked.voice_phone_call) {
            dispatch(addToCollection('VoicePhoneCall', 'all', Object.values(data.linked.voice_phone_call)));
          }
          if (data.linked.person) {
            dispatch(addToCollection('Person', 'all', Object.values(data.linked.person)));
          }
        });
        messageBroker.addMessageListener('agent.voice.incoming-call-answered', (data) => {
          const state = getState();
          const me = meSelector(state);

          // don't remove incoming call notification for other agents
          // just stop it ringing in other browser tabs
          if (parseInt(me.get('id'), 10) !== parseInt(data.agent_id, 10)) {
            return;
          }

          dispatch(removeIncomingCall(data));
        });
        messageBroker.addMessageListener('agent.voice.outgoing-call-answered', (data) => {
          const state = getState();
          const connections = connectionsSelector(state);
          const connection = connections.filter(c => filterConnection(c, data.call_sid)).first();

          if (connection) {
            closeIframes();

            dispatch(resetOutgoingCall());
            connection.ticketId = data.ticket_id;
            connection.callId   = data.call_id;

            const routeUrl = `/agent/tickets/${data.ticket_id}`;
            if (!window.DeskPRO_Window.TabBar.findTabByRouteUrl(routeUrl)) {
              window.DeskPRO_Window.runPageRoute(`ticket:${routeUrl}`, { noToggle: true });
            }
          }
        });
        messageBroker.addMessageListener('agent.voice.outgoing-call-declined', (data) => {
          const state = getState();
          const connections = connectionsSelector(state);
          const connection = connections.filter(c => filterConnection(c, data.CallSid)).first();

          dispatch(resetOutgoingCall());
          if (connection) {
            hangupConnection(connection);
          }
        });
        messageBroker.addMessageListener('agent.voice.conference.hold', (data) => {
          dispatch(updateConnectionState({
            call_id: parseInt(data.call_id, 10),
            state:   {
              hold: !!data.hold
            }
          }));
        });
        messageBroker.addMessageListener('agent.voice.outgoing-provider-error', (errors) => {
          const state = getState();
          const outgoingCall = outgoingCallSelector(state);
          if (outgoingCall) {
            dispatch(resetOutgoingCall());
            window.AgentVoiceDropdown.showProviderError(outgoingCall.get('callTo'), errors);
          }
        });
      })
      .catch((e) => {
        console.log(e);

        // catch mic disabled exception
        // nothing to do
      });

      accounts.forEach((account) => {
        const id = account.get('id');
        const type = account.get('type');
        const credentials = account.get('client_credentials');

        if (type === 'twilio') {
          try {
            const options = {
              debug: true
            };

            clients[id] = new window.Twilio.Device(credentials.get('phone_token'), options);
            clients[id].ready(() => {
              console.log('phone ready');
            });
            clients[id].error((error) => {
              console.log('device error');
              console.log(error);
            });
            clients[id].connect((connection) => {
              connection.ticketId = connection.message.TicketId;
              connection.callId   = connection.message.CallId;
              connection.outbound = connection.message.Outbound;

              dispatch(addConnection(connection));
              connection.disconnect(() => {
                // call has ended
                // unset incoming and outgoing calls
                dispatch(removeConnection(connection));
                dispatch(resetOutgoingCall());
              });
            });
          } catch (e) {
            console.error(e.message);
          }
        } else if (type === 'plivo') {
          try {
            const username = credentials.getIn(['endpoint', 'username']);
            const password = credentials.getIn(['endpoint', 'password']);
            const options = {
              debug:            'DEBUG',
              permOnClick:      true,
              audioConstraints: {
                optional: [
                  { googAutoGainControl: false },
                  { googEchoCancellation: false }
                ]
              },
              enableTracking: true
            };

            const refreshEndpoint = () => {
              // regenerate token if not found for some reason
              const me = meSelector(initialState);
              api.sendPost(`DP_API/voice_accounts/plivo/${id}/refresh_endpoint/${me.get('id')}`).success(({ data }) => {
                clients[id].client.login(data.username, data.password);
              });
            };

            clients[id] = new window.Plivo(options);
            clients[id].client.on('onLoginFailed', refreshEndpoint);
            clients[id].client.on('onCalling', () => {
              dispatch(addConnection(clients[id].client));
            });
            clients[id].client.on('onCallTerminated', () => {
              // call has ended
              // unset incoming and outgoing calls
              dispatch(removeConnection(clients[id].client));
              dispatch(resetOutgoingCall());

              delete clients[id].client.ticketId;
              delete clients[id].client.callId;
              delete clients[id].client.outbound;
            });

            if (username && password) {
              clients[id].client.login(username, password);
            } else {
              refreshEndpoint();
            }
          } catch (e) {
            console.error(e.message);
          }
        }
      });
    }
  }
);

export const makeOutboundCall = createAction(
  'VOICE_AGENT_MAKE_OUTBOUND_PHONE_CALL',
  (callFrom, callTo, ticketId = null) => (dispatch, getState) => {
    const state   = getState();
    const me      = meSelector(state);
    const agentId = me.get('id');
    const numbers = allNumbersSelector(state);
    const promise = api.sendPost('DP_API/voice_client/prepare_outbound_call?include=person', {
      call_from: callFrom,
      call_to:   callTo,
      ticket:    ticketId
    });
    promise.success(({ data, linked }) => {
      if (linked.person) {
        dispatch(addToCollection('VoicePhoneCall', 'all', Object.values(linked.person)));
      }

      const number      = numbers.get(callFrom);
      const accounts    = allVoiceAccountsSelector(state);
      const accountId   = number.get('account');
      const account     = accounts.get(accountId);
      const accountType = account.get('type');

      dispatch(setOutgoingCall({ callFrom: number, callTo, phoneCall: data }));
      if (accountType === 'twilio') {
        clients[accountId].connect({
          CallId:   data.id,
          AgentId:  agentId,
          From:     number.get('number'),
          To:       callTo,
          Outbound: true
        });
      } else if (accountType === 'plivo') {
        clients[accountId].client.call(callTo, {
          'X-PH-CallId':   data.id,
          'X-PH-AgentId':  agentId,
          'X-PH-From':     number.get('number'),
          'X-PH-To':       callTo,
          'X-PH-Outbound': true
        });

        clients[accountId].client.agentId  = agentId;
        clients[accountId].client.callId   = data.id;
        clients[accountId].client.outbound = true;

        dispatch(addConnection(clients[accountId].client));
      }
    });

    return promise;
  }
);

export const toggleHold = createAction(
  'VOICE_AGENT_TOGGLE_HOLD',
  (callId, hold) => api.sendPut(`DP_API/voice_client/phone_call/${callId}/hold_call`, { hold })
);

export const acceptPhoneCall = createAction(
  'VOICE_AGENT_ACCEPT_PHONE_CALL',
  incomingCall => (dispatch, getState) => {
    const state       = getState();
    const me          = meSelector(state);
    const agentId     = me.get('id');
    const callId      = incomingCall.get('call_id');
    const accountId   = incomingCall.get('account_id');
    const accounts    = allVoiceAccountsSelector(state);
    const account     = accounts.get(accountId);
    const accountType = account.get('type');

    if (!account || !clients[accountId]) {
      return;
    }

    // create and open a ticket
    let promise;
    // if incoming call has a task than means it's a new incoming call
    // otherwise we've got an invitation or call transfer
    if (incomingCall.get('task')) {
      promise = api.sendPut(`DP_API/voice_client/phone_call/${callId}/accept_call`);
    } else {
      promise = api.sendPut(`DP_API/voice_client/phone_call/${callId}/assign_agent`);
    }

    promise.success(({ data }) => {
      closeIframes();

      dispatch(removeIncomingCall(incomingCall));
      if (accountType === 'twilio') {
        clients[accountId].connect({
          CallId:   callId,
          AgentId:  agentId,
          TicketId: data.id
        });
      } else if (accountType === 'plivo') {
        clients[accountId].client.call('accept', {
          'X-PH-CallId':   callId,
          'X-PH-AgentId':  agentId,
          'X-PH-TicketId': data.id,
          'X-PH-CallTime': (new Date()).getTime()
        });

        clients[accountId].client.ticketId = data.id;
        clients[accountId].client.callId   = callId;
      }

      if (incomingCall.get('call_type') === 'transfer' && incomingCall.get('invite_type') === 'cold') {
        dispatch(toggleHold(callId, false));
      }

      window.DeskPRO_Window.runPageRoute(`ticket:/agent/tickets/${data.id}`, { noToggle: true });
    });
  }
);

export const declinePhoneCall = createAction(
  'VOICE_AGENT_DECLINE_PHONE_CALL',
  incomingCall => (dispatch, getState) => {
    const state = getState();
    const me = meSelector(state);

    if (!incomingCall) {
      return;
    }

    dispatch(removeIncomingCall(incomingCall));

    const callId = incomingCall.get('call_id');
    if (incomingCall.get('task')) {
      // got an incoming call
      api.sendPut(`DP_API/voice_client/phone_call/${callId}/reject_call`);
    } else {
      // got an invite, decline
      api.sendPut(`DP_API/voice_client/phone_call/${callId}/ignore_invite/${me.get('id')}`);
    }
  }
);

export const toggleMute = createAction(
  'VOICE_AGENT_TOGGLE_MUTE',
  (connection, mute) => {
    // mute on client side
    if (connection.getCallUUID) {
      // plivo
      if (mute) {
        connection.mute();
      } else {
        connection.unmute();
      }
    } else {
      // twilio
      connection.mute(mute);
    }

    // send participant mute request
    api.sendPut(`DP_API/voice_client/phone_call/${connection.callId}/mute_call`, { mute });
  }
);

export const warmAddAgent = createAction(
  'VOICE_AGENT_ADD',
  (connection, agent) =>
    api.sendPut(`DP_API/voice_client/phone_call/${connection.callId}/warm_add/${agent.get('id')}`)
);

export const warmTransferCall = createAction(
  'VOICE_AGENT_TRANSFER_CALL',
  (connection, agent) => (dispatch) => {
    dispatch(toggleHold(connection.callId, true));
    return api.sendPut(`DP_API/voice_client/phone_call/${connection.callId}/warm_transfer/${agent.get('id')}`);
  }
);

export const coldTransferCall = createAction(
  'VOICE_AGENT_TRANSFER_CALL',
  (connection, agent) =>
     api
      .sendPut(`DP_API/voice_client/phone_call/${connection.callId}/cold_transfer/${agent.get('id')}`)
      .success(() => {
        hangupConnection(connection);
      })
);

export const cancelInvite = createAction(
  'VOICE_AGENT_CANCEL_INVITE',
  (callId, agent) => api.sendPut(`DP_API/voice_client/phone_call/${callId}/cancel_invite/${agent.get('id')}`)
);

export const checkIsActive = createAction(
  'VOICE_AGENT_CHECK_IS_ACTIVE',
  callId => api.sendGet(`DP_API/voice_client/phone_call/${callId}/is_active`)
);

export const hangup = createAction(
  'VOICE_AGENT_HANGUP',
  connection => (dispatch) => {
    dispatch(resetOutgoingCall());
    api.sendPut(`DP_API/voice_client/phone_call/${connection.callId}/end_call`).success(() => {
      hangupConnection(connection);
    });
  }
);

export const searchPerson = createAction(
  'VOICE_AGENT_SEARCH_PERSON',
  searchString => api.sendGet(`DP_API/search/person?${compileParams({
    q:      searchString,
    params: { with_phone_number: 1 }
  })}`)
);

export const openDialpad = createAction(
  'VOICE_AGENT_OPEN_DIALPAD',
  (outgoingNumber, ticketId = null, ticketTitle = null) => {
    window.AgentVoiceDropdown.openDialpad(outgoingNumber, ticketId, ticketTitle);
  }
);

export const deleteRecord = createAction(
  'VOICE_AGENT_DELETE_RECORD',
  phoneCallId => api.sendDelete(`DP_API/voice_phone_calls/${phoneCallId}/record`)
);

