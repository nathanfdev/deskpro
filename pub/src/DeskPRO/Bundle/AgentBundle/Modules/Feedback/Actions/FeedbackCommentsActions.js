import { createAction } from 'Ampliflux';
import * as Feedback from 'DeskPRO/Bundle/AgentBundle/Services/Api/Feedback';
import { loadFeedback } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackActions';
import { currentListParamsSelector } from '../Selectors/list';
import { setCurrentListParams, commentsToReview } from './FeedbackListActions';
import { loadPeople } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';
import { loadEmails } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/emailsActions';
import Moment from 'moment';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'feedback';

export const getFeedbackForComments = createAction(
  'FEEDBACK_COMMENTS_GET_FEEDBACK',
    ids => dispatch => dispatch(loadFeedback(recordStoresId, ids))
);


export const getAuthors = createAction(
  'FEEDBACK_COMMENTS_GET_AUTHORS',
  (comments) => (dispatch) => {
    const ids = [];
    const unique = {};
    for (var key in comments.data) {
      if (comments.data.hasOwnProperty(key)) {
        if (typeof(unique[comments.data[key].person_id]) === 'undefined') {
          ids.push(comments.data[key].person_id);
        }
        unique[comments.data[key].person_id] = 0;
      }
    }
    dispatch(loadEmails(recordStoresId, ids));
    return dispatch(loadPeople(recordStoresId, ids));
  }
);

export const loadCommentsList = createAction(
  'FEEDBACK_COMMENTS_LIST',
  (overwriteParams = {}) => (dispatch, getState) => {
    const currentParams = currentListParamsSelector(getState()).toJS();
    let params = { ...currentParams, ...overwriteParams };

    dispatch(setCurrentListParams(params));
    delete params.isComments;
    const {navItem} = params;
    if (navItem) {
      delete params.navItem;
      params = { ...params, ...navItem };
    }
    const {filters} = params;
    if (filters) {
      delete params.filters;
      for (var property in filters) {
        if (filters.hasOwnProperty(property)) {
          if (property === 'date_created') {
            for (var dateProperty in filters[property]) {
              if (filters[property].hasOwnProperty(dateProperty) && filters[property][dateProperty]) {
                params[dateProperty] = Moment(filters[property][dateProperty]).format('YYYY-MM-DD HH:mm:ss');
              }
            }
          } else {
            params[property] = filters[property];
          }
        }
      }
    }
    return Feedback.commentsToReviewList(params).then(promise => {
      const comments = promise.getData();
      const ids = [];
      for (var index in comments.data) {
        if (comments.data.hasOwnProperty(index)) {
          ids.push(comments.data[index].feedback_id);
        }
      }
      dispatch(getFeedbackForComments(ids));
      dispatch(getAuthors(comments));
      return comments;
    });
  }
);

export const setTableSort = createAction(
  'FEEDBACK_COMMENTS_SET_TABLE_SORT',
  (sort, order) => dispatch => {
    dispatch(loadCommentsList({ sort: sort, order: order }));
    return { sort, order };
  }
);

export const commentsToggleOrder = createAction(
  'FEEDBACK_COMMENTS_TOGGLE_ORDER',
  (order) => dispatch => {
    dispatch(loadCommentsList({ sort: 'date_created', order: order }));
    return order;
  }
);

export const deleteComment = createAction(
  'FEEDBACK_COMMENTS_DELETE',
  (id) => dispatch => {
    Feedback.deleteFeedbackComment(id).then(()=> {
      dispatch(commentsToReview());
      dispatch(loadCommentsList());
    });
    return id;
  }
);

export const editComment = createAction(
  'FEEDBACK_COMMENTS_EDIT',
  (data) => dispatch => {
    const commentId = data.commentId;
    delete data.commentId;
    Feedback.editComment(commentId, data).then(()=> {
      dispatch(commentsToReview());
      dispatch(loadCommentsList());
    });
  }
);
