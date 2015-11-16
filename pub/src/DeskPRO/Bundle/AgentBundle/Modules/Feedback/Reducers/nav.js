import { createReducer } from 'Ampliflux';
import { async, setFullPayload, mergeFullPayload, setValue } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/FeedbackListActions';
import Immutable from 'immutable';

const initialState = {
  toValidateCount: 0,
  commentsToReviewCount: 0,
  labels: [/* string */],
  types: [/* {title, value} */],
  customCategories: [/* {title, value} */],
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

export default createReducer(initialState, {
  [actions.feedbackToValidate]: async({
    success: (state, payload) =>
      state.setIn(['toValidateCount'], payload.data.count)
  }),
  [actions.commentsToReview]: async({
    success: (state, payload) =>
      state.setIn(['commentsToReviewCount'], payload.data.count)
  }),
  [actions.feedbackLabels]: async({
    success: (state, payload) =>
      state.setIn(['labels'], Immutable.fromJS(payload.data))
  }),
  [actions.feedbackTypes]: async({
    success: (state, payload) => state.set('types', Immutable.fromJS(payload.data))
  }),
  [actions.feedbackCustomCategories]: async({
    success: (state, payload) =>
      state.setIn(['customCategories'], Immutable.fromJS(payload.data.nested))
  }),
  [actions.feedbackNew]: async({
    success: (state, payload) =>
      state.setIn(['statuses', 'new'], payload.data.count)
  }),
  [actions.feedbackActiveStatus]: async({
    success: (state, payload) =>
      state.setIn(['statuses', 'active'], payload.data)
  }),
  [actions.feedbackClosedStatus]: async({
    success: (state, payload) =>
      state
        .setIn(['statuses', 'closed'], payload.data)
  }),
  [actions.feedbackHiddenStatus]: async({
    success: (state, payload) =>
      state
        .setIn(['statuses', 'hidden'], payload.data)
  }),
  [actions.loadLabels]: async({success: setFullPayload('labels')}),

  [actions.initialLoad]: async({
    success: mergeFullPayload(),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  })
});
