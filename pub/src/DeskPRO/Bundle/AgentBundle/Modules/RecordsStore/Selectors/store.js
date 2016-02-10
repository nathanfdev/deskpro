import Immutable from 'immutable';
import { createSelector, defaultMemoize } from 'reselect';

const storeSelector = state => state.RecordsStore.store;

export function isLoadedCollectionSelectorFactory(recordName, collectionName) {
  return createSelector(
    storeSelector,
    state => state.getIn([recordName, 'statuses', collectionName, 'success']) === true
             && !state.getIn([recordName, 'statuses', collectionName, 'loading'])
  );
}

export function collectionSelectorFactory(recordName, collectionName) {
  return createSelector(
    storeSelector,
    state => {
      let result;
      if (state.hasIn([recordName, 'collections', collectionName]) && state.hasIn([recordName, 'records'])) {
        const ids = state.getIn([recordName, 'collections', collectionName]);
        result = state.getIn([recordName, 'records']).filter((record, id) => ids.includes(parseInt(id)));
      } else {
        result = Immutable.fromJS({});
      }

      return result;
    }
  );
}

export function allSelectorFactory(recordName) {
  return collectionSelectorFactory(recordName, 'all');
}
