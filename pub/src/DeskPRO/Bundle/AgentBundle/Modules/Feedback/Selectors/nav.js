import { createSelector } from 'reselect';
import { reduceImmutableToProperty } from 'DeskPRO/Component/Util/Map';

const stateSelector = state => state.Feedback.nav;

export const isDoneSelector = createSelector(
  stateSelector,
    state => state.getIn(['async', 'done'])
);

export const typeCountersSelector = createSelector(
  stateSelector,
    state => state.get('types')
);

export const categoryCountersSelector = createSelector(
  stateSelector,
    state => state.get('categories')
);

export const statusCountersSelector = createSelector(
  stateSelector,
    state => state.get('statuses')
);

export const feedbackLabelsSelector = createSelector(
  stateSelector,
    state => reduceImmutableToProperty('label', state.get('labels'))
);
