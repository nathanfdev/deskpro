import { createAction } from 'DeskPRO/Component/Ampliflux';
import { loadAll, addToCollection, updateCollection, removeFromCollection, releaseCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { api, repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import Immutable from 'immutable';

export const loadAccounts = createAction(
  'VOICE_LOAD_ACCOUNTS',
  () => dispatch => dispatch(loadAll('VoiceAccount'))
);

export const testCredentials = createAction(
  'VOICE_TEST_CREDENTIALS',
  data => api.sendPost('DP_API/voice_accounts/test_credentials', data)
);

export const createAccount = createAction(
  'VOICE_CREATE_ACCOUNT',
  data => dispatch => repository('VoiceAccount').create(data).success((response) => {
    const account = Immutable.fromJS(response.data);
    dispatch(addToCollection('VoiceAccount', 'all', Immutable.List([account])));
  })
);

export const updateAccount = createAction(
  'VOICE_UPDATE_ACCOUNT',
  (id, data) => dispatch => repository('VoiceAccount').update(data, id).success(() => {
    const account = Immutable.fromJS({ ...data, id });
    dispatch(updateCollection('VoiceAccount', Immutable.List([account]), 'merge'));
  })
);

export const deleteAccount = createAction(
  'VOICE_DELETE_ACCOUNT',
  id => dispatch => repository('VoiceAccount').remove(id).success(() => {
    dispatch(removeFromCollection('VoiceAccount', 'all', [id]));

    // reset related data
    dispatch(releaseCollection('VoiceQueue', 'all'));
    dispatch(releaseCollection('VoiceAutoAttendant', 'all'));
  })
);
