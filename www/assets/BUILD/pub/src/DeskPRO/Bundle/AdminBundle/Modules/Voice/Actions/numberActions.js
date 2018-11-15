import { createAction } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import toastr from 'toastr';
import { repository, api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import { loadAll, addToCollection, updateCollection, removeFromCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const changeExistingNumbersFilter = createAction('VOICE_CHANGE_EXISTING_NUMBERS_FILTER');
export const changeAvailableNumbersFilter = createAction('VOICE_CHANGE_AVAILABLE_NUMBERS_FILTER');

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
  (accountId, page = 0) => api.sendGet(`DP_API/voice_accounts/twilio/${accountId}/existing_numbers?page=${page}`)
);

export const loadAvailableNumbers = createAction(
  'VOICE_LOAD_AVAILABLE_NUMBERS',
  (accountId, params) => api.sendGet(`DP_API/voice_accounts/twilio/${accountId}/available_numbers?${compileParams(params)}`)
);

export const addAvailableNumber = createAction(
  'VOICE_ADD_AVAILABLE_NUMBER',
  number => api.sendPost(`DP_API/voice_accounts/twilio/${number.get('account')}/buy_number`, {
    number: number.get('number')
  }).error((response) => {
    if (response.errors && response.errors.errors && response.errors.errors[0]) {
      toastr.error(response.errors.errors[0].message);
    }
  })
);
