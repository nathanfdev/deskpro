import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

const sel = (state) => state.RecordStores.Example.widgetTypes;

const stateSelector = createStoreSelectors(sel);
const createRequestSelector = createRequestSelectorsBuilder(stateSelector);

const allSels = createRequestSelector('all');

export const typesStatus = allSels.statusSel;
export const typesSelector = allSels.recordsSel;
