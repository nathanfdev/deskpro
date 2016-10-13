import { createAction } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import { repository, api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { setCollection, updateCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const loadAgents = createAction(
  'ADMIN_LOAD_AGENTS',
  () => dispatch => api.sendGet('DP_API/agents').success((response) => {
    dispatch(setCollection('Person', 'agents', response.data));
  })
);

export const editPerson = createAction(
  'ADMIN_EDIT_AGENT',
  (id, data) => dispatch => repository('Person').update(data, id).success(() => {
    const person = Immutable.fromJS({ ...data, id });
    dispatch(updateCollection('Person', Immutable.List([person]), 'merge'));
  })
);
