import { createAction } from 'Ampliflux';
import * as Feedback from 'DeskPRO/Bundle/AgentBundle/Services/Api/Feedback';
import { loadFeedback } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackActions';
import { commentsToReview } from './FeedbackListActions';
import { applyParams } from './FeedbackListActions';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'feedback';

export const getFeedbackForComments = createAction(
  'FEEDBACK_COMMENTS_GET_FEEDBACK',
    ids => dispatch => dispatch(loadFeedback(recordStoresId, ids))
);

export const deleteComment = createAction(
  'FEEDBACK_COMMENTS_DELETE',
  (id) => dispatch => {
    Feedback.deleteFeedbackComment(id).then(()=> {
      dispatch(commentsToReview());
      dispatch(applyParams({isComments: true}));
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
      dispatch(applyParams({isComments: true}));
    });
  }
);
