import { createAction } from 'Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { setCollection } from 'DeskPRO/Bundle/AgentBundle/Modules/RecordsStore';
import { applyParams } from './FeedbackListActions';
import { setPeopleRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'feedback';

const prepareLinkedData = (linked) => {
  const result = [];
  for (const key in linked) {
    if (linked.hasOwnProperty(key)) {
      result.push(linked[key]);
    }
  }
  return result;
};

export const commentsToReviewCounter = createAction(
  'FEEDBACK_COMMENTS_TO_REVIEW_COUNTER',
  () => repository('FeedbackComment').commentsToReview().then(promise => promise.getData())
);

export const deleteComment = createAction(
  'FEEDBACK_COMMENTS_DELETE',
  (ids) => dispatch => {
    repository('FeedbackComment').removeBatch(ids).then(()=> {
      dispatch(commentsToReviewCounter());
      dispatch(applyParams({ isComments: true }));
    });
    return ids;
  }
);

export const approveComment = createAction(
  'FEEDBACK_COMMENTS_APPROVE',
  (ids) => dispatch => {
    repository('FeedbackComment').approveFeedbackComment(ids).then(()=> {
      dispatch(commentsToReviewCounter());
      dispatch(applyParams({ isComments: true }));
    });
    return ids;
  }
);

export const editComment = createAction(
  'FEEDBACK_COMMENTS_EDIT',
  (data) => dispatch => {
    repository('FeedbackComment').update(data).then(()=> {
      dispatch(commentsToReviewCounter());
      dispatch(applyParams({ isComments: true }));
    });
  }
);

export const loadFeedbackCommentsList = createAction(
  'FEEDBACK_LIST_OF_COMMENTS',
    params => (dispatch) => repository('FeedbackComment').commentsToReviewList(params).then(promise => {
      const res = promise.getData();
      const ids = res.data.map(item=>item.id);

      dispatch(setCollection('FeedbackComment', recordStoresId, res.data));
      dispatch(setCollection('Feedback', recordStoresId, prepareLinkedData(res.linked.feedback)));
      dispatch(setPeopleRequest(recordStoresId, prepareLinkedData(res.linked.person)));

      return { ids: ids, pagination: res.meta.pagination };
    }
  ));
