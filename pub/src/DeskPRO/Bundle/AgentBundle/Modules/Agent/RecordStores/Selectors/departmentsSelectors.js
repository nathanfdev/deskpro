import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';
import { createSelector } from 'reselect';
import { reduceImmutableToProperty } from 'DeskPRO/Component/Util/Map';

function departmentsStateSel(state) {
  return state.RecordStores.Agent.departments;
}

export const departmentsStateSelector = createStoreSelectors(departmentsStateSel);
export const createDepartmentsRequestSelectors = createRequestSelectorsBuilder(departmentsStateSelector);

export const allDepartmentsSelector = createDepartmentsRequestSelectors('all').recordsSel;

export const myDepartmentsSelector = createDepartmentsRequestSelectors('my').recordsSel;
export const myDepartmentsStatusSelector = createDepartmentsRequestSelectors('my').statusSel;

export const departmentNamesSelector = createSelector(
  allDepartmentsSelector,
  agents => reduceImmutableToProperty('title', agents)
);
