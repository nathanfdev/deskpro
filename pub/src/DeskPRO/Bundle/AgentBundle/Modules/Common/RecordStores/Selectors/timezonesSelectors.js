import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function timezonesStateSel(state) {
  return state.RecordStores.Common.timezones;
}

export const timezonesStateSelector = createStoreSelectors(timezonesStateSel);
export const createLanguagesRequestSelectors = createRequestSelectorsBuilder(timezonesStateSelector);

export const timezonesSelector = createSelector(
  createLanguagesRequestSelectors('all').recordsSel,
  timezones => timezones
);
