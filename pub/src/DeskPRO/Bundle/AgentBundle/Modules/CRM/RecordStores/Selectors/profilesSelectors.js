import { createSelector } from 'reselect';
import Immutable from 'immutable';

import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function profileStateSel(state) {
  return state.RecordStores.CRM.profiles;
}

export const profileStateSelector = createStoreSelectors(profileStateSel);
export const createProfileRequestSelectors = createRequestSelectorsBuilder(profileStateSelector);

export const myStateSelector = createProfileRequestSelectors('my');
export const mySelector = createSelector(
  myStateSelector.recordsSel,
  profiles => profiles.first() || Immutable.fromJS({})
);

export const myStatusSelector = createSelector(
  myStateSelector.statusSel,
  profiles => profiles
);
