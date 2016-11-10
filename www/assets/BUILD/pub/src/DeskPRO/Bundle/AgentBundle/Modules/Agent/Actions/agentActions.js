import { createAction } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { updateCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const editAgent = createAction(
  'ADMIN_EDIT_AGENT',
  (id, data) => dispatch => repository('Person').update(data, id).success(() => {
    const person = Immutable.fromJS({ ...data, id });
    dispatch(updateCollection('Person', Immutable.List([person]), 'merge'));
  })
);
