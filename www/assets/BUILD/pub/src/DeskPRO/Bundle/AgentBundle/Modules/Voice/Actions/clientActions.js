import { createAction } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import { storageAvailable } from 'DeskPRO/Component/Util/storageAvailable';
import { loadBatch, addToCollection, updateCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { phoneTokenSelector, connectionsSelector, incomingCallSelector } from '../Selectors/client';
import { allPhoneCallsSelector } from '../Selectors/phoneCalls';
import { allNumbersSelector } from '../Selectors/numbers';
import { closeIframes } from '../../Application/Actions/bootstrapActions';
import { actionAlertsSelector } from '../../Application/Selectors/notifications';

export const setMicEnabled = createAction('VOICE_AGENT_SET_MIC_ENABLED');
export const setVoiceTokens = createAction('VOICE_AGENT_SET_TOKENS');
export const setVoiceActivities = createAction('VOICE_AGENT_SET_ACTIVITIES');
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
    const phoneToken = phoneTokenSelector(initialState);

    if (!phoneToken) {
      return;
    }

    // check if mic is enabled
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
      navigator.mediaDevices.getUserMedia({ audio: true }).then((stream) => {
        // mark the mic as enabled
        stream.stop();
        dispatch(setMicEnabled(true));

        try {
          window.Twilio.Device.setup(phoneToken, {
            debug: true
          });
        } catch (e) {
          // handle invalid token
        }

        // if pusher is enabled we need to use an another polling action
        const actionAlerts = actionAlertsSelector(initialState);
        const hasPusher = actionAlerts.clients.filter(notifyClient =>
          notifyClient.type === 'pusher' || notifyClient.type === 'deskpro'
        ).length > 0;

        if (hasPusher) {
          setInterval(() => {
            api.sendGet('/agent/ping-voice-worker');
          }, 5000);
        }

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

          dispatch(addIncomingCall(data));
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

          if (data.call_id === incomingCall.get('call_id')) {
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

          dispatch(addIncomingCall(data));
        });
        messageBroker.addMessageListener('agent.voice.conference.participant-cancel', (data) => {
          dispatch(removeIncomingCall(data));
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
        });
        messageBroker.addMessageListener('agent.voice.voicemail.new-message', (event) => {
          const data = event.data;

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
          const connection = connections.filter(c => c.parameters.CallSid === data.CallSid).first();

          if (connection) {
            closeIframes();

            dispatch(resetOutgoingCall());
            connection.message.TicketId = data.ticket.id;

            const routeUrl = `/agent/tickets/${data.ticket.id}`;
            if (!window.DeskPRO_Window.TabBar.findTabByRouteUrl(routeUrl)) {
              window.DeskPRO_Window.runPageRoute(`ticket:${routeUrl}`);
            }
          }
        });
        messageBroker.addMessageListener('agent.voice.outgoing-call-declined', (data) => {
          const state = getState();
          const connections = connectionsSelector(state);
          const connection = connections.filter(c => c.parameters.CallSid === data.CallSid).first();

          dispatch(resetOutgoingCall());
          if (connection) {
            connection.disconnect();
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
        messageBroker.addMessageListener('agent.voice.conference.status', (data) => {
          dispatch(updateConnectionState({
            call_id: parseInt(data.phone_call.id, 10),
            state:   {
              participants: data.agent_participants,
              hold:         !!data.hold
            }
          }));
        });
      })
      .catch((e) => {
        console.log(e);

        // catch mic disabled exception
        // nothing to do
      });

      try {
        window.Twilio.Device.setup(phoneToken, { debug: true });
        window.Twilio.Device.ready(() => {
          console.log('phone ready');
        });
        window.Twilio.Device.error((error) => {
          console.log('device error');
          console.log(error);
        });
        window.Twilio.Device.connect((connection) => {
          dispatch(addConnection(connection));
          connection.disconnect(() => {
            // call has ended
            // unset incoming and outgoing calls and set worker activity to idle
            dispatch(removeConnection(connection));
            dispatch(resetOutgoingCall());
          });
        });
      } catch (e) {
        console.error(e.message);
      }
    }
  }
);

export const makeOutboundCall = createAction(
  'VOICE_AGENT_MAKE_OUTBOUND_PHONE_CALL',
  (callFrom, callTo, ticketId = null) => (dispatch, getState) => {
    const state   = getState();
    const me      = meSelector(state);
    const agentId = me.get('id');
    // const busySid = busyActivitySidSelector(state);
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

      const number = numbers.get(callFrom);
      dispatch(setOutgoingCall({ callFrom: number, callTo, phoneCall: data }));
      // worker.update('ActivitySid', busySid);
      window.Twilio.Device.connect({
        CallId:   data.id,
        AgentId:  agentId,
        From:     number.get('number'),
        To:       callTo,
        Outbound: true
      });
    });

    return promise;
  }
);

export const acceptPhoneCall = createAction(
  'VOICE_AGENT_ACCEPT_PHONE_CALL',
  incomingCall => (dispatch, getState) => {
    const state   = getState();
    const me      = meSelector(state);
    const agentId = me.get('id');
    const callId  = incomingCall.get('call_id');

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
      window.Twilio.Device.connect({
        CallId:   callId,
        AgentId:  agentId,
        TicketId: data.id
      });

      window.DeskPRO_Window.runPageRoute(`ticket:/agent/tickets/${data.id}`);
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
    connection.mute(mute);

    // send participant mute request
    const callId = connection.message.CallId;
    api.sendPut(`DP_API/voice_client/phone_call/${callId}/mute_call`, { mute });
  }
);

export const toggleHold = createAction(
  'VOICE_AGENT_TOGGLE_HOLD',
  (connection, hold) => (dispatch) => {
    const callId = connection.message.CallId;
    dispatch(updateConnectionState({ call_id: parseInt(callId, 10), state: { hold } }));

    return api.sendPut(`DP_API/voice_client/phone_call/${callId}/hold_call`, { hold });
  }
);

export const addAgent = createAction(
  'VOICE_AGENT_ADD',
  (connection, agent, type) => {
    const callId = connection.message.CallId;
    return api.sendPut(`DP_API/voice_client/phone_call/${callId}/add/${agent.get('id')}/${type}`);
  }
);

export const transferCall = createAction(
  'VOICE_AGENT_TRANSFER_CALL',
  (connection, agent, type) => (dispatch) => {
    dispatch(toggleHold(connection, true));

    const callId = connection.message.CallId;
    return api.sendPut(`DP_API/voice_client/phone_call/${callId}/transfer/${agent.get('id')}/${type}`).success(() => {
      if (type === 'cold') {
        connection.disconnect();
      }
    });
  }
);

export const cancelInvite = createAction(
  'VOICE_AGENT_CANCEL_INVITE',
  (callId, agent, type) => (dispatch, getState) => {
    const state   = getState();
    const me      = meSelector(state);
    const agentId = me.get('id');
    const from    = `client:deskpro${agentId}`;

    const promise = api.sendPut(`DP_API/voice_client/phone_call/${callId}/cancel_invite/${agent.get('id')}`);
    promise.success(() => {
      if (type === 'cold') {
        window.Twilio.Device.connect({
          From:    from,
          CallId:  callId,
          AgentId: agentId
        });
      }
    });

    return promise;
  }
);

export const hangup = createAction(
  'VOICE_AGENT_HANGUP',
  (connection) => {
    connection.disconnect();

    const callId = connection.message.CallId;
    api.sendPut(`DP_API/voice_client/phone_call/${callId}/end_call`);
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
