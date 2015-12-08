import { createAction } from 'Ampliflux';
import { deleteFeedbackComment, editFeedbackComment, approveFeedbackComment, commentsToReviewList, commentsToReview }
  from 'DeskPRO/Bundle/AgentBundle/Services/Api/FeedbackComment';
import { applyParams } from './FeedbackListActions';
import { setFeedbackRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackActions';
import { setPeopleRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'feedback';

export const commentsToReviewCounter = createAction(
  'FEEDBACK_COMMENTS_TO_REVIEW_COUNTER',
  () => commentsToReview().then(promise => promise.getData())
);

export const deleteComment = createAction(
  'FEEDBACK_COMMENTS_DELETE',
  (ids) => dispatch => {
    deleteFeedbackComment(ids).then(()=> {
      dispatch(commentsToReviewCounter());
      dispatch(applyParams({ isComments: true }));
    });
    return ids;
  }
);

export const approveComment = createAction(
  'FEEDBACK_COMMENTS_APPROVE',
  (ids) => dispatch => {
    approveFeedbackComment(ids).then(()=> {
      dispatch(commentsToReviewCounter());
      dispatch(applyParams({ isComments: true }));
    });
    return ids;
  }
);

export const editComment = createAction(
  'FEEDBACK_COMMENTS_EDIT',
  (data) => dispatch => {
    const commentId = data.commentId;
    delete data.commentId;
    editFeedbackComment(commentId, data).then(()=> {
      dispatch(commentsToReviewCounter());
      dispatch(applyParams({ isComments: true }));
    });
  }
);

export const loadFeedbackCommentsList = createAction(
  'FEEDBACK_LIST_OF_COMMENTS',
    params => (dispatch) => commentsToReviewList(params).then(promise => {
      const comments = promise.getData();
      const feedback = [];
      for (var index in comments.linked.feedback) {
        if (comments.linked.feedback.hasOwnProperty(index)) {
          feedback.push(comments.linked.feedback[index]);
        }
      }
      const people = [];
      for (const key in comments.linked.person) {
        if (comments.linked.person.hasOwnProperty(key)) {
          people.push(comments.linked.person[key]);
        }
      }
      dispatch(setFeedbackRequest(recordStoresId, feedback));
      dispatch(setPeopleRequest(recordStoresId, people));
      return comments;
    }
  ));
