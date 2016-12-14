import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { callsEnabledSelector } from '../Selectors/agents';
import { phoneTokenSelector, workerTokenSelector, idleActivitySidSelector, busyActivitySidSelector, offlineActivitySidSelector } from '../Selectors/client';

export const setVoiceTokens = createAction('VOICE_AGENT_SET_TOKENS');
export const setVoiceActivities = createAction('VOICE_AGENT_SET_ACTIVITIES');
export const addIncomingCall = createAction('VOICE_AGENT_ADD_RESERVATION');
export const removeIncomingCall = createAction('VOICE_AGENT_REMOVE_RESERVATION');
export const removeConferenceIncomingCalls = createAction('VOICE_AGENT_REMOVE_CONFERENCE_RESERVATIONS');
export const addConnection = createAction('VOICE_AGENT_ADD_CONNECTION');
export const removeConnection = createAction('VOICE_AGENT_REMOVE_CONNECTION');

let worker;

export const voiceBootstrap = createAction(
  'VOICE_AGENT_BOOTSTRAP',
  () => (dispatch, getState) => {
    const state        = getState();
    const callsEnabled = callsEnabledSelector(state);
    const phoneToken   = phoneTokenSelector(state);
    const workerToken  = workerTokenSelector(state);
    const idleSid      = idleActivitySidSelector(state);
    const offlineSid   = offlineActivitySidSelector(state);
    const connectSid   = callsEnabled ? idleSid : offlineSid;

    if (!workerToken || !phoneToken) {
      return;
    }

    worker = new window.Twilio.TaskRouter.Worker(workerToken, true, connectSid, offlineSid);
    worker.on('ready', () => {
      console.log('worker ready');
    });
    worker.on('reservation.created', (reservation) => {
      console.log('reservation.created');
      console.log(reservation);
      dispatch(addIncomingCall(reservation));
    });
    worker.on('reservation.accepted', () => {
      console.log('reservation.accepted');
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
      dispatch(removeIncomingCall(reservation));
      worker.update('ActivitySid', idleSid);
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
        const callId = connection.message.CallId;
        console.log(connection);

        dispatch(addConnection(connection));
        window.DeskPRO_Window.runPageRoute(`voice:/agent/voice/in-progress/${callId}/${connection.parameters.CallSid}`);

        connection.disconnect(() => {
          // call has ended
          // unset incoming call and set worker activity to idle
          dispatch(removeConnection(connection));
          worker.update('ActivitySid', idleSid);
        });
      });
    } catch (e) {
      console.error(e.message);
    }

    const messageBroker = window.DeskPRO_Window.getMessageBroker();
    messageBroker.addMessageListener('agent.voice.conference.participant-invite', (data) => {
      dispatch(addIncomingCall(data));
    });
    messageBroker.addMessageListener('agent.voice.conference.participant-cancel', (data) => {
      dispatch(removeIncomingCall(data));
    });
    messageBroker.addMessageListener('agent.voice.conference.status', (event) => {
      const eventName = event.StatusCallbackEvent;
      if (eventName === 'conference-end') {
        dispatch(removeConferenceIncomingCalls(event.ConferenceSid));
        worker.update('ActivitySid', idleSid);
      }
    });
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
  (connection, mute) => connection.mute(mute)
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

    if (type === 'cold') {
      connection.disconnect();
    }

    const callId = connection.message.CallId;
    return api.sendPut(`DP_API/voice_client/phone_call/${callId}/transfer/${agent.get('id')}/${type}`);
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
    const callId = connection.message.CallId;

    connection.disconnect();
    api.sendPut(`DP_API/voice_client/phone_call/${callId}/end_call`);
  }
);
