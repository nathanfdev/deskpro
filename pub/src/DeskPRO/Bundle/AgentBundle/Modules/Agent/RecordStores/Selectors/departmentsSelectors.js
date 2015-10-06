import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';
import { createSelector } from 'reselect';

function departmentsStateSel(state) {
  return state.RecordStores.Agent.departments;
}

export const departmentsStateSelector = createStoreSelectors(departmentsStateSel);
export const createDepartmentsRequestSelectors = createRequestSelectorsBuilder(departmentsStateSelector);

export const allDepartmentsSelector = createDepartmentsRequestSelectors('all').recordsSel;
