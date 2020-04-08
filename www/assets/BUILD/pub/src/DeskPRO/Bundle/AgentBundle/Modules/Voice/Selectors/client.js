import { createSelector } from 'reselect';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';

const stateSelector = state => state.Voice.client;

export const isSecure = window.location.protocol === 'https:' || window.location.hostname === 'localhost';

export const hasSocketConnectionSelector = createSelector(
  stateSelector,
  state => state.get('hasSocketConnection')
);

export const isVoiceMicEnabled = createSelector(
  stateSelector,
  state => state.get('micEnabled')
);

export const voiceSettingsSelector = createSelector(
  stateSelector,
  state => state.get('settings')
);

export const agentVoicemailTimeoutSelector = createSelector(
  voiceSettingsSelector,
  settings => settings && settings.get('agent_voicemail_timeout')
);

// connections
export const incomingCallsSelector = createSelector(
  stateSelector,
  state => state.get('incomingCalls')
);

export const incomingCallSelector = createSelector(
  incomingCallsSelector,
  incomingCalls => incomingCalls.first()
);

export const connectionsSelector = createSelector(
  stateSelector,
  state => state.get('connections')
);

export const waitingConnectionSelector = createSelector(
  stateSelector,
  state => state.get('waitingConnection')
);

export const outboundNumberSelector = createSelector(
  stateSelector,
  state => state.get('outboundNumber')
);

export const outgoingCallSelector = createSelector(
  stateSelector,
  state => state.get('outgoingCall')
);

export const isVoiceAvailableSelector = createSelector(
  meSelector,
  me => me.getIn(['agent_data', 'is_voice_enabled'])
);

export const canInitVoiceSelector = createSelector(
  isVoiceAvailableSelector,
  isVoiceAvailable => isVoiceAvailable && isSecure
);

export const isVoiceEnabledSelector = createSelector(
  isVoiceAvailableSelector,
  hasSocketConnectionSelector,
  (isVoiceAvailable, hasSocketConnection) => isVoiceAvailable && isSecure && hasSocketConnection
);

// settings
export const ringingVolumeSelector = createSelector(
  stateSelector,
  state => state.get('ringingVolume')
);

export const busyAgentsSelector = createSelector(
  stateSelector,
  state => state.get('onlineAgents')
    .filter(onlineStatus => onlineStatus.get('busy_for_voice'))
    .map(onlineStatus => onlineStatus.get('agent_id'))
);

export const onlineAgentsSelector = createSelector(
  stateSelector,
  state => state.get('onlineAgents')
    .filter(onlineStatus => onlineStatus.get('online') && onlineStatus.get('voice_enabled'))
    .map(onlineStatus => onlineStatus.get('agent_id'))
);


export const forwardingAgentsSelector = createSelector(
  stateSelector,
  state => state.get('onlineAgents')
    .filter(onlineStatus => onlineStatus.get('forwarding_enabled'))
    .map(onlineStatus => onlineStatus.get('agent_id'))
);
