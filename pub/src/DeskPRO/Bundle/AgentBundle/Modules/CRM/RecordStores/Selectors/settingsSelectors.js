import { createSelector } from 'reselect';
import Immutable from 'immutable';

import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function profileStateSel(state) {
  return state.RecordStores.CRM.settings;
}

export const settingStateSelector = createStoreSelectors(profileStateSel);
export const createSettingsRequestSelectors = createRequestSelectorsBuilder(settingStateSelector);

export const myStateSelector = createSettingsRequestSelectors('my');
export const mySelector = createSelector(
  myStateSelector.recordsSel,
  settings => settings.first() || Immutable.fromJS({})
);

export const myStatusSelector = createSelector(
  myStateSelector.statusSel,
  settings => settings
);
