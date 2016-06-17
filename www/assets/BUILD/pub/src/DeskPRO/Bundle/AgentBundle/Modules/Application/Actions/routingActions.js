import { createAction } from 'DeskPRO/Component/Ampliflux';

export const hashChanged = createAction('APP_ROUTING_HASH_CHANGED');
export const updateRoutingState = createAction(
  'APP_ROUTING_UPDATE_HASH_STATE_OPTION',
  (component, option, value) => ({ component, option, value })
);
