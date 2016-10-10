import { createAction } from 'DeskPRO/Component/Ampliflux';
import { loadAll, addToCollection, updateCollection, removeFromCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { api, repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import Immutable from 'immutable';

export const loadAccounts = createAction(
  'TWILIO_LOAD_ACCOUNTS',
  () => dispatch => dispatch(loadAll('TwilioAccount'))
);

export const testCredentials = createAction(
  'TWILIO_TEST_CREDENTIALS',
  data => api.sendPost('DP_API/twilio_accounts/test_credentials', data)
);

export const createAccount = createAction(
  'TWILIO_CREATE_ACCOUNT',
  data => dispatch => repository('TwilioAccount').create(data).success((response) => {
    const account = Immutable.fromJS(response.data);
    dispatch(addToCollection('TwilioAccount', 'all', Immutable.List([account])));
  })
);

export const updateAccount = createAction(
  'TWILIO_UPDATE_ACCOUNT',
  (id, data) => dispatch => repository('TwilioAccount').update(data, id).success(() => {
    const account = Immutable.fromJS({ ...data, id });
    dispatch(updateCollection('TwilioAccount', Immutable.List([account]), 'merge'));
  })
);

export const deleteAccount = createAction(
  'TWILIO_DELETE_ACCOUNT',
  id => dispatch => repository('TwilioAccount').remove(id).success(() => {
    dispatch(removeFromCollection('TwilioAccount', 'all', [id]));
  })
);
