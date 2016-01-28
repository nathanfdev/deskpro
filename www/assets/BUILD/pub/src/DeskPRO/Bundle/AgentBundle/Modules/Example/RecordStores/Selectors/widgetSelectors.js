import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

const sel = (state) => state.RecordStores.Example.widgets;

const stateSelector = createStoreSelectors(sel);
const createRequestSelector = createRequestSelectorsBuilder(stateSelector);

export const listFilterSelector = createRequestSelector('view_filter');
