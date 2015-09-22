import { createReducer } from 'Ampliflux';
import { async, setFullPayload } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/FeedbackListActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'

const initialState = {
  groups: [
    {name: 'awaiting_validation', value: 1, current: true},
    {name: 'status', value: '', current: false},
    {name: 'category', value: '', current: false},
    {name: 'custom_category', value: '', current: false}
  ],
  toValidateCount: 0,
  commentsToReviewCount: 0,
  labels: [/* string */],
  types: [/* {title, value} */],
  customCategories: [/* {title, value} */],
  statuses: {
    new: 0,
    active: {
      count: 0,
      nested: [/* {count, group} */]
    },
    closed: {
      count: 0,
      nested: [/* {count, group} */]
    },
    hidden: {
      count: 0,
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
      state.setIn(['labels'], payload.data)
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
  [actions.changeGroupState]: (state, payload) => {
    let groups = [];
    state.get('groups').toJS().forEach(obj=> {
      const nextObj   = {...obj};
      nextObj.current = obj.field === payload;
      groups.push(nextObj);
    });
    return state.set('groups', Immutable.fromJS(groups))
  }
});
