import { createSelector } from 'reselect';

const stateSelector = state => state.Feedback.nav;

export const isLoadedSelector = createSelector(
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
  state => state.get('labels')
);

export const feedbackToReviewCountSelector = createSelector(
  stateSelector,
  state => state.get('feedbackToReviewCount')
);

export const commentsToReviewCountSelector = createSelector(
  stateSelector,
  state => state.get('commentsToReviewCount')
);
