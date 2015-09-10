import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function userStateSel(state) {
  return state.Common.users;
}

export const userStateSelector = createStoreSelectors(userStateSel);
export const createUserRequestSelectors = createRequestSelectorsBuilder(userStateSelector);
