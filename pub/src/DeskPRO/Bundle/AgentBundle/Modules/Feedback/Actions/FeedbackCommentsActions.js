import { createAction } from 'Ampliflux';
import * as Feedback from 'DeskPRO/Bundle/AgentBundle/Services/Api/Feedback';
import { loadFeedback } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackActions';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'feedback';

export const getFeedbackForComments = createAction(
  'FEEDBACK_COMMENTS_GET_FEEDBACK',
    ids => dispatch => dispatch(loadFeedback(recordStoresId, ids))
);


export const loadCommentsList = createAction(
  'FEEDBACK_COMMENTS_LIST',
  (params = {}) => dispatch => Feedback.commentsToReviewList(params).then(promise => {
    const comments = promise.getData();
    const ids = [];
    for (var ind in comments.data) {
      ids.push(comments.data[ind].feedback_id);
    }
    dispatch(getFeedbackForComments(ids));
    return comments;
  })
);

export const setTableSort = createAction(
  'FEEDBACK_COMMENTS_SET_TABLE_SORT',
  (sort, order) => dispatch => {
    dispatch(loadCommentsList({sort: sort, order: order}));
    return {sort, order};
  }
);