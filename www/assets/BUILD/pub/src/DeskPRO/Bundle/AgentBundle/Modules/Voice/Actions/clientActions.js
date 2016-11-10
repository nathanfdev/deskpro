import { createAction } from 'DeskPRO/Component/Ampliflux';
import { phoneTokenSelector, workerTokenSelector, idleActivitySidSelector, offlineActivitySidSelector, reservationSelector } from '../Selectors/client';

let worker;
let connection;

export const setVoiceTokens = createAction('VOICE_AGENT_SET_TOKENS');
export const setVoiceActivities = createAction('VOICE_AGENT_SET_ACTIVITIES');
export const setReservation = createAction('VOICE_AGENT_SET_RESERVATION');

export const voiceBootstrap = createAction(
  'VOICE_AGENT_BOOTSTRAP',
  () => (dispatch, getState) => {
    const state = getState();
    const phoneToken = phoneTokenSelector(state);
    const workerToken = workerTokenSelector(state);
    const idleSid = idleActivitySidSelector(state);
    const offlineSid = offlineActivitySidSelector(state);

    if (workerToken) {
      worker = new window.Twilio.TaskRouter.Worker(workerToken, true, idleSid, offlineSid);
      worker.on('ready', () => {
        console.log('worker ready');
      });
      worker.on('reservation.created', (reservation) => {
        console.log('reservation.created');
        console.log(reservation);
        dispatch(setReservation(reservation));
      });
      worker.on('reservation.accepted', (reservation) => {
        console.log('reservation.accepted');
        console.log(reservation);
        dispatch(setReservation(reservation));
      });
      worker.on('reservation.canceled', (reservation) => {
        console.log('reservation.canceled');
        console.log(reservation);
        dispatch(setReservation(null));
      });
      worker.on('reservation.timeout', (reservation) => {
        console.log('reservation.timeout');
        console.log(reservation);
      });
      worker.on('reservation.rescinded', (reservation) => {
        console.log('reservation.rescinded');
        console.log(reservation);
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
    }

    if (phoneToken) {
      try {
        window.Twilio.Device.setup(phoneToken, { debug: true });
        window.Twilio.Device.ready(() => {
          console.log('phone ready');
        });
        window.Twilio.Device.error((error) => {
          console.log('device error');
          console.log(error);
        });
        window.Twilio.Device.connect((conn) => {
          console.log(conn);
          console.log('successfully established call');
        });
        window.Twilio.Device.incoming((conn) => {
          connection = conn;
          connection.disconnect(() => {
            // call has ended
            // unset incoming call and set worker activity to idle
            console.log('call has ended');

            dispatch(setReservation(null));
            connection = null;
            worker.update('ActivitySid', idleSid);
          });
        });
      } catch (e) {
        console.error(e.message);
      }
    }
  }
);

export const acceptPhoneCall = createAction(
  'VOICE_AGENT_ACCEPT_PHONE_CALL',
  () => connection.accept()
);

export const declinePhoneCall = createAction(
  'VOICE_AGENT_DECLINE_PHONE_CALL',
  () => (dispatch, getState) => {
    const state = getState();
    const reservation = reservationSelector(state);

    if (!reservation) {
      return;
    }

    dispatch(setReservation(null));
    connection.reject();
  }
);

export const hangup = createAction(
  'VOICE_AGENT_HANGUP',
  () => (dispatch) => {
    dispatch(setReservation(null));
    window.Twilio.Device.disconnectAll();
  }
);
