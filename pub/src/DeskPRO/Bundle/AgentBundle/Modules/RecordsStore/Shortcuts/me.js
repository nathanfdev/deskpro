import { createSelector } from 'reselect';
import { collectionSelectorFactory } from '../index';

const meStateSelector = collectionSelectorFactory('Person', 'me');
export const meSelector = createSelector(meStateSelector, users => users.first());
