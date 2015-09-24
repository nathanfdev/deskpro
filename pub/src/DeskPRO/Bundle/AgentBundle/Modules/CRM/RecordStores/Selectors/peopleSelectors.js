import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';
import { reduceMapToProperty } from 'DeskPRO/Component/Util/Map';

function peopleStateSel(state) {
  return state.RecordStores.people;
}

export const peopleStateSelector = createStoreSelectors(peopleStateSel);
export const createPeopleRequestSelectors = createRequestSelectorsBuilder(peopleStateSelector);
