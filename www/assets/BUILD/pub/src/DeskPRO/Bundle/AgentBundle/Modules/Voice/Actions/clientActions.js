import { createAction } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import { storageAvailable } from 'DeskPRO/Component/Util/storageAvailable';
import { loadBatch, addToCollection, updateCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { callsEnabledSelector } from '../Selectors/agents';
import { phoneTokenSelector, workerTokenSelector, idleActivitySidSelector, busyActivitySidSelector, offlineActivitySidSelector, connectionsSelector } from '../Selectors/client';
import { allPhoneCallsSelector } from '../Selectors/phoneCalls';
import { allNumbersSelector } from '../Selectors/numbers';
import { closeIframes } from '../../Application/Actions/bootstrapActions';

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
export const openDialpad = createAction('VOICE_AGENT_OPEN_DIALPAD');
export const dialpadOpened = createAction('VOICE_AGENT_DIALPAD_OPENED');
export const setOutgoingCall = createAction('VOICE_AGENT_SET_OUTGOING_CALL');
export const resetOutgoingCall = createAction('VOICE_AGENT_RESET_OUTGOING_CALL');

export const setRingingVolume = createAction(
  'VOICE_AGENT_SET_RINGING_VOLUME',
  (value) => {
    if (storageAvailable('localStorage')) {
      localStorage.setItem('dpAgent.voice.ringingVolume', value);
    }

    return value;
  }
);

let worker;

export const voiceBootstrap = createAction(
  'VOICE_AGENT_BOOTSTRAP',
  () => (dispatch, getState) => {
    const initialState = getState();
    const callsEnabled = callsEnabledSelector(initialState);
    const phoneToken   = phoneTokenSelector(initialState);
    const workerToken  = workerTokenSelector(initialState);
    const idleSid      = idleActivitySidSelector(initialState);
    const offlineSid   = offlineActivitySidSelector(initialState);
    const connectSid   = callsEnabled ? idleSid : offlineSid;

    if (!workerToken || !phoneToken || !connectSid || !offlineSid) {
      return;
    }

    // check if mic is enabled
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
      navigator.mediaDevices.getUserMedia({ audio: true }).then((stream) => {
        // mark the mic as enabled
        stream.stop();
        dispatch(setMicEnabled(true));

        // init twilio worker
        worker = new window.Twilio.TaskRouter.Worker(workerToken, true, connectSid, offlineSid);
        worker.on('ready', () => {
          console.log('worker ready');
        });
        worker.on('reservation.created', (reservation) => {
          const personId = reservation.task.attributes.deskpro_person_id;
          if (personId) {
            dispatch(loadBatch('Person', personId, 'all'));
          }

          dispatch(addIncomingCall(reservation));
        });
        worker.on('reservation.accepted', () => {
          console.log('reservation.accepted');
          window.DeskPRO_Window.getMessageChanneler().poller.setInterval(2000);
        });
        worker.on('reservation.canceled', (reservation) => {
          console.log('reservation.canceled');
          dispatch(removeIncomingCall(reservation));
          worker.update('ActivitySid', idleSid);
        });
        worker.on('reservation.timeout', (reservation) => {
          console.log('reservation.timeout');
          dispatch(removeIncomingCall(reservation));
          worker.update('ActivitySid', idleSid);
        });
        worker.on('reservation.rescinded', (reservation) => {
          console.log('reservation.rescinded');
          worker.update('ActivitySid', idleSid);

          // another agent have already accepted the call
          if (reservation.task.assignmentStatus === 'assigned') {
            console.log('reservation.workerSid');
            dispatch(updateIncomigCall(reservation));

            // fetch the ticket info to get assigned agent
            const callId = reservation.task.attributes.deskpro_call_id;
            const fetchTimeout = setInterval(() => {
              api.sendGet(`DP_API/voice_client/phone_call/${callId}/ticket`).success(({ data }) => {
                if (data.agent) {
                  clearInterval(fetchTimeout);

                  reservation.task.attributes.deskpro_assigned_agent = data.agent;
                  dispatch(updateIncomigCall(reservation));

                  // remove the reservation by timeout to show that another agent accepted the call
                  setTimeout(() => dispatch(removeIncomingCall(reservation)), 5000);
                }
              });
            }, 500);
          } else {
            dispatch(removeIncomingCall(reservation));
          }
        });
        worker.on('connected', (data) => {
          console.log('worker connected');
          console.log(data);
        });
        worker.on('disconnected', (data) => {
          console.log('worker disconnected');
          console.log(data);
        });
        worker.on('error', (data) => {
          console.log('worker error');
          console.log(data);
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
            // if we have call id on device connect then it means we get an incoming phone call
            // assign agent to the phone call's ticket
            if (!connection.message.Outbound) {
              // create and open a ticket
              api
                .sendPut(`DP_API/voice_client/phone_call/${connection.message.CallId}/assign_agent`)
                .success(({ data }) => {
                  closeIframes();

                  connection.message.TicketId = data.id;
                  window.DeskPRO_Window.runPageRoute(`ticket:/agent/tickets/${data.id}`);
                });
            }

            dispatch(addConnection(connection));
            connection.disconnect(() => {
              // call has ended
              // unset incoming and outgoing calls and set worker activity to idle
              dispatch(removeConnection(connection));
              dispatch(resetOutgoingCall());

              worker.update('ActivitySid', idleSid);
            });
          });
        } catch (e) {
          console.error(e.message);
        }

        const messageBroker = window.DeskPRO_Window.getMessageBroker();
        messageBroker.addMessageListener('agent.voice.calls_enabled', (data) => {
          const state  = getState();
          const agents = agentsSelector(state);

          let agent  = agents.get(data.person_id);
          if (!agent) {
            return;
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
            worker.update('ActivitySid', idleSid);
          }
        });
        messageBroker.addMessageListener('agent.voice.voicemail.new-message', (event) => {
          const data = event.data;

          dispatch(addToCollection('VoicemailRecord', 'all', [data.data]));

          if (data.linked.voice_phone_call) {
            dispatch(addToCollection('VoicePhoneCall', 'all',  Object.values(data.linked.voice_phone_call)));
          }
          if (data.linked.person) {
            dispatch(addToCollection('Person', 'all',  Object.values(data.linked.person)));
          }
        });
        messageBroker.addMessageListener('agent.voice.incoming-call-answered', (data) => {
          dispatch(removeIncomingCall(data));
        });
        messageBroker.addMessageListener('agent.voice.outgoing-call-answered', (data) => {
          const state       = getState();
          const connections = connectionsSelector(state);
          const connection  = connections.filter(c => c.parameters.CallSid === data.CallSid).first();

          if (connection) {
            closeIframes();

            dispatch(resetOutgoingCall());
            connection.message.TicketId = data.ticket.id;
            window.DeskPRO_Window.runPageRoute(`ticket:/agent/tickets/${data.ticket.id}`);
          }
        });
      })
      .catch(() => {
        // catch mic disabled exception
        // nothing to do
      });
    }
  }
);

export const makeOutboundCall = createAction(
  'VOICE_AGENT_MAKE_OUTBOUND_PHONE_CALL',
  (callFrom, callTo) => (dispatch, getState) => {
    const state   = getState();
    const me      = meSelector(state);
    const agentId = me.get('id');
    const busySid = busyActivitySidSelector(state);
    const numbers = allNumbersSelector(state);
    const promise = api.sendPost('DP_API/voice_client/prepare_outbound_call?include=person', {
      call_from: callFrom,
      call_to:   callTo
    });
    promise.success(({ data, linked }) => {
      if (linked.person) {
        dispatch(addToCollection('VoicePhoneCall', 'all', Object.values(linked.person)));
      }

      const number = numbers.get(callFrom);
      dispatch(setOutgoingCall({ callFrom: number, callTo, phoneCall: data }));
      worker.update('ActivitySid', busySid);
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
    const from    = `client:deskpro${agentId}`;
    const busySid = busyActivitySidSelector(state);

    dispatch(removeIncomingCall(incomingCall));

    if (incomingCall.task) {
      // accept twilio reservation
      incomingCall.accept(() => {
        window.Twilio.Device.connect({
          From:    from,
          CallId:  incomingCall.task.attributes.deskpro_call_id,
          AgentId: agentId
        });
      });
    } else {
      // got invite, join the conference
      worker.update('ActivitySid', busySid);
      window.Twilio.Device.connect({
        From:    from,
        CallId:  incomingCall.get('call_id'),
        AgentId: agentId
      });
    }
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

    if (incomingCall.task) {
      // handle twilio reservation
      api.sendPut(`DP_API/voice_client/reject_call/${incomingCall.task.sid}`).success(() => {
        incomingCall.reject();
      });
    } else {
      // got invite, decline
      const callId = incomingCall.get('call_id');
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
  (connection, hold) => {
    const callId = connection.message.CallId;
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

    return api.sendPut(`DP_API/voice_client/phone_call/${callId}/cancel_invite/${agent.get('id')}`).success(() => {
      if (type === 'cold') {
        window.Twilio.Device.connect({
          From:    from,
          CallId:  callId,
          AgentId: agentId
        });
      }
    });
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
