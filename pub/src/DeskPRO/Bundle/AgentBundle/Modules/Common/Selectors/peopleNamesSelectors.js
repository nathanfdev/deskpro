import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function peopleNamesStateSel(state) {
  return state.Common.peopleNames;
}

export const peopleNamesStateSelector = createStoreSelectors(peopleNamesStateSel);
export const createPeopleNamesRequestSelectors = createRequestSelectorsBuilder(peopleNamesStateSelector);
