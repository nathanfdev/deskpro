import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function organizationsStateSel(state) {
  return state.RecordStores.CRM.organizations;
}

export const organizationsStateSelector = createStoreSelectors(organizationsStateSel);
export const createOrganizationsRequestSelectors = createRequestSelectorsBuilder(organizationsStateSelector);
