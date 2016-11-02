import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { setFullPayload, setValue } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/templatesActions';

const initialState = {
  expandedNumber: null,
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
  [actions.changeAvailableNumbersFilter]: setFullPayload('availableFilter'),
  [actions.expandNumber]:                 setFullPayload('expandedNumber'),
  [actions.collapseNumber]:               setValue('expandedNumber', null)
});
