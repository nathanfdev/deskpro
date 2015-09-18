import { createSelector } from 'reselect';

const stateSelector = state => state.Feedback.nav;

export const sortingDataSelector = createSelector(
  stateSelector,
    list => list.get('sortOptions').toJS().find(option => option.current === true)
);

export const viewDataSelector = createSelector(
  stateSelector,
    list => list.get('viewModeOptions').toJS().find(option => option.current === true)
);
