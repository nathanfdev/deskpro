import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function peopleStateSel(state) {
  return state.RecordStores.people;
}

export const peopleStateSelector = createStoreSelectors(peopleStateSel);
export const createPeopleRequestSelectors = createRequestSelectorsBuilder(peopleStateSelector);
