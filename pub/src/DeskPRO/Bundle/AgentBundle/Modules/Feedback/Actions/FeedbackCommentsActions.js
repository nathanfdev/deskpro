import { createAction } from 'Ampliflux';
import * as Feedback from 'DeskPRO/Bundle/AgentBundle/Services/Api/Feedback';
import { loadFeedback } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackActions';
import { commentsToReview } from './FeedbackListActions';
import { loadPeople } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';
import { loadEmails } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/emailsActions';

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
      if (typeof(unique[comments.data[key].person_id]) === 'undefined') {
        ids.push(comments.data[key].person_id);
      }
      unique[comments.data[key].person_id] = 0;
    }
    dispatch(loadEmails(recordStoresId, ids));
    return dispatch(loadPeople(recordStoresId, ids));
  }
);

export const setCommentsViewMode = createAction(
  'FEEDBACK_SET_COMMENTS_VIEW_MODE',
    value => value
);

export const loadCommentsList = createAction(
  'FEEDBACK_COMMENTS_LIST',
  (params = {}) => dispatch => {
    delete params.comments;
    return Feedback.commentsToReviewList(params).then(promise => {
      const comments = promise.getData();
      const ids = [];
      for (var index in comments.data) {
        if (comments.data.hasOwnProperty(index)) {
          ids.push(comments.data[index].feedback_id);
        }
      }
      dispatch(setCommentsViewMode(true));
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
    order => dispatch => {
    dispatch(loadCommentsList({ sort: 'date_created', order: order }));
    return order;
  }
);

export const deleteComment = createAction(
  'FEEDBACK_COMMENTS_DELETE',
    id => dispatch => {
    Feedback.deleteFeedbackComment(id).then(()=> {
      dispatch(commentsToReview());
      dispatch(loadCommentsList());
    });
    return id;
  }
);

export const editComment = createAction(
  'FEEDBACK_COMMENTS_EDIT',
    data => dispatch => {
    const commentId = data.commentId;
    delete data.commentId;
    Feedback.editComment(commentId, data).then(()=> {
      dispatch(commentsToReview());
      dispatch(loadCommentsList());
    });
  }
);
