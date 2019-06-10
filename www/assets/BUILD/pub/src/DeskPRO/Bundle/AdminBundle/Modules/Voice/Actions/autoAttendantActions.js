import { createAction } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { loadAll, addToCollection, updateCollection, removeFromCollection, releaseCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const loadAutoAttendants = createAction(
  'VOICE_LOAD_AUTO_ATTENDANTS',
  () => dispatch => dispatch(loadAll('VoiceAutoAttendant'))
);

export const createAutoAttendant = createAction(
  'VOICE_CREATE_AUTO_ATTENDANT',
  data => dispatch => repository('VoiceAutoAttendant').create(data).success((response) => {
    const autoAttendant = Immutable.fromJS(response.data);
    dispatch(addToCollection('VoiceAutoAttendant', 'all', Immutable.List([autoAttendant])));
    dispatch(releaseCollection('Person', 'agents'));
  })
);

export const editAutoAttendant = createAction(
  'VOICE_EDIT_AUTO_ATTENDANT',
  (id, data) => dispatch => repository('VoiceAutoAttendant').update(data, id).success(() => {
    const autoAttendant = Immutable.fromJS({ ...data, id });
    dispatch(updateCollection('VoiceAutoAttendant', Immutable.List([autoAttendant]), 'merge'));
    dispatch(releaseCollection('Person', 'agents'));
  })
);

export const deleteAutoAttendant = createAction(
  'VOICE_DELETE_AUTO_ATTENDANT',
  id => dispatch => repository('VoiceAutoAttendant').remove(id).success(() => {
    dispatch(removeFromCollection('VoiceAutoAttendant', 'all', [id]));
  })
);
