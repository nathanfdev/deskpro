import { createAction } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import toastr from 'toastr';
import { repository, api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import { loadAll, addToCollection, updateCollection, removeFromCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { allAccountsSelector } from '../Selectors/account';

export const loadNumbers = createAction(
  'VOICE_LOAD_NUMBERS',
  () => dispatch => dispatch(loadAll('VoiceNumber'))
);

export const createNumber = createAction(
  'VOICE_EDIT_NUMBER',
  data => dispatch => repository('VoiceNumber').create(data).success((response) => {
    dispatch(addToCollection('VoiceNumber', 'all', Immutable.List([Immutable.fromJS(response.data)])));
  })
);

export const editNumber = createAction(
  'VOICE_EDIT_NUMBER',
  (id, data) => dispatch => repository('VoiceNumber').update(data, id).success(() => {
    const number = Immutable.fromJS({ ...data, id });
    dispatch(updateCollection('VoiceNumber', Immutable.List([number]), 'merge'));
  })
);

export const deleteNumber = createAction(
  'VOICE_DELETE_NUMBER',
  id => dispatch => repository('VoiceNumber').remove(id).success(() => {
    dispatch(removeFromCollection('VoiceNumber', 'all', [id]));
  })
);

export const loadExistingNumbers = createAction(
  'VOICE_LOAD_EXISTING_NUMBERS',
  (account, page = 0) => api.sendGet(`DP_API/voice_accounts/${account.get('type')}/${account.get('id')}/existing_numbers?page=${page}`)
);

export const loadAvailableNumbers = createAction(
  'VOICE_LOAD_AVAILABLE_NUMBERS',
  (account, params) => api.sendGet(`DP_API/voice_accounts/${account.get('type')}/${account.get('id')}/available_numbers?${compileParams(params)}`)
);

export const addAvailableNumber = createAction(
  'VOICE_ADD_AVAILABLE_NUMBER',
  number => (dispatch, getState) => {
    const state = getState();
    const accounts = allAccountsSelector(state);
    const account = accounts.get(number.get('account'));
    if (!account) {
      return null;
    }

    return api.sendPost(`DP_API/voice_accounts/${account.get('type')}/${account.get('id')}/buy_number`, {
      number: number.get('number')
    }).error((response) => {
      if (response.errors && response.errors.errors && response.errors.errors[0]) {
        toastr.error(response.errors.errors[0].message);
      }
    });
  }
);
