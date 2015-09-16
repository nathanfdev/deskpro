import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function departmentsStateSel(state) {
  return state.Common.departments;
}

export const departmentsStateSelector = createStoreSelectors(departmentsStateSel);
export const createDepartmentsRequestSelectors = createRequestSelectorsBuilder(departmentsStateSelector);
