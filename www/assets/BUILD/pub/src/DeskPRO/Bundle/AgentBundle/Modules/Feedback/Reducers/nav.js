import { createReducer } from 'Ampliflux';
import { async, setFullPayload, mergeFullPayload, setValue } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/feedbackNavActions';
import * as commentsActions from '../Actions/FeedbackCommentsActions';
import Immutable from 'immutable';

export const feedbackNavInitialState = {
  async: {
    done: false
  },
  types: {},
  categories: {},
  feedbackToReviewCount: { count: 0 },
  commentsToReviewCount: { count: 0 },
  labels: [/* string */],
  statuses: {
    new: 0,
    active: {
      count: 0,
      group: 'active',
      nested: [/* {count, group} */]
    },
    closed: {
      count: 0,
      group: 'closed',
      nested: [/* {count, group} */]
    },
    hidden: {
      count: 0,
      group: 'hidden',
      nested: [/* {count, group} */]
    }
  }
};

export default createReducer(feedbackNavInitialState, {
  [actions.feedbackToValidateCounter]: async({
    success: (state, payload) =>
      state.setIn(['toValidateCount'], payload.data.count)
  }),
  [commentsActions.commentsToReviewCounter]: async({
    success: (state, payload) =>
      state.setIn(['commentsToReviewCount'], payload.data.count)
  }),
  [actions.feedbackTypes]: async({
    success: (state, payload) => state.set('types', Immutable.fromJS(payload.data))
  }),
  [actions.feedbackCustomCategories]: async({
    success: (state, payload) =>
      state.setIn(['customCategories'], Immutable.fromJS(payload.data.nested))
  }),

  [actions.loadLabels]: async({ success: setFullPayload('labels') }),

  [actions.initialLoad]: async({
    success: mergeFullPayload(),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  })
});
