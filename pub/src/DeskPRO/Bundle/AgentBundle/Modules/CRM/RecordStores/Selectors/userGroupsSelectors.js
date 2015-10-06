import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';
import { reduceMapToProperty } from 'DeskPRO/Component/Util/Map';

function userGroupsStateSel(state) {
  return state.RecordStores.CRM.userGroups;
}

export const userGroupsStateSelector = createStoreSelectors(userGroupsStateSel);
export const createUserGroupsRequestSelectors = createRequestSelectorsBuilder(userGroupsStateSelector);
export const userGroupsSelector = createUserGroupsRequestSelectors('all').recordsSel;
export const userGroupNamesSelector = createSelector(
  userGroupsSelector,
  groups => reduceMapToProperty('title', groups.toJS())
);