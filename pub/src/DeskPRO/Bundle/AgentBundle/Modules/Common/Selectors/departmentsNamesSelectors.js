import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function departmentsNamesStateSel(state) {
  return state.Common.departmentsNames;
}

export const departmentsNamesStateSelector = createStoreSelectors(departmentsNamesStateSel);
export const createDepartmentsNamesRequestSelectors = createRequestSelectorsBuilder(departmentsNamesStateSelector);
