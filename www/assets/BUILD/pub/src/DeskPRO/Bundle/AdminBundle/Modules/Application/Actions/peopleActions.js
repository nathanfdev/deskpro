import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const loadAgents = createAction(
  'ADMIN_LOAD_AGENTS',
  () => dispatch => api.sendGet('DP_API/agents').success((response) => {
    dispatch(setCollection('Person', 'agents', response.data));
  })
);
