import { createSelector } from 'reselect';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';

const isSecure = window.location.protocol === 'https:' || window.location.hostname === 'localhost';
const stateSelector = state => state.Voice.client;

export const isVoiceEnabledSelector = createSelector(
  meSelector,
  me => isSecure && me.getIn(['agent_data', 'is_voice_enabled'])
);

// tokens
export const tokensSelector = createSelector(
  stateSelector,
  state => state.get('tokens')
);

export const phoneTokenSelector = createSelector(
  tokensSelector,
  tokens => tokens && tokens.get('phone_token')
);

export const workerTokenSelector = createSelector(
  tokensSelector,
  tokens => tokens && tokens.get('worker_token')
);

// activities
export const activitiesSelector = createSelector(
  stateSelector,
  state => state.get('activities')
);

export const idleActivitySidSelector = createSelector(
  activitiesSelector,
  activities => activities && activities.get('idle_sid')
);

export const idleDisabledActivitySidSelector = createSelector(
  activitiesSelector,
  activities => activities && activities.get('idle_disabled_sid')
);

export const busyActivitySidSelector = createSelector(
  activitiesSelector,
  activities => activities && activities.get('busy_sid')
);

export const reservedActivitySidSelector = createSelector(
  activitiesSelector,
  activities => activities && activities.get('reserved_sid')
);

export const offlineActivitySidSelector = createSelector(
  activitiesSelector,
  activities => activities && activities.get('offline_sid')
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
