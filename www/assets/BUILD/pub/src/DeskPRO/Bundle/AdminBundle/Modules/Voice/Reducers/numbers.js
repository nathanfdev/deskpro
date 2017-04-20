import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { setFullPayload } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/numberActions';

const initialState = {
  existingFilter: {
    account: null
  },
  availableFilter: {
    account:      null,
    country_code: null,
    region:       null,
    types:        ['local', 'tollFree', 'mobile'],
    phrase:       ''
  }
};

export default createReducer(initialState, {
  [actions.changeExistingNumbersFilter]:  setFullPayload('existingFilter'),
  [actions.changeAvailableNumbersFilter]: setFullPayload('availableFilter')
});
