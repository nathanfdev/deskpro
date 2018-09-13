import { createSelector } from 'reselect';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';

const stateSelector = state => state.Voice.client;

export const isSecure = window.location.protocol === 'https:' || window.location.hostname === 'localhost';

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

export const isVoiceEnabledSelector = createSelector(
  isVoiceAvailableSelector,
  isVoiceAvailable => isVoiceAvailable && isSecure
);

// settings
export const ringingVolumeSelector = createSelector(
  stateSelector,
  state => state.get('ringingVolume')
);

export const connectionStatesSelector = createSelector(
  stateSelector,
  state => state.get('connectionStates')
);
