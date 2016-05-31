import { createAction } from 'Ampliflux';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import { repository, api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { setDisplayFields } from './FeedbackListActions';
import { defaultCardFields, defaultTableFields, defaultCommentTableFields }
  from '../Components/List/ControlBar/FeedbackViewOptions';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'feedback';

export const initialLoad = createAction(
  'FEEDBACK_NAV_INITIAL_LOAD',
  () => (dispatch) => new Promise(
    (resolve) => {
      const batch = 'DP_API/batch'
        + '?get[categories]=DP_API/feedback/counts?group_by%3Dcustom_category'
        + '&get[types]=DP_API/feedback/counts?group_by%3Dcategory'
        + '&get[labels]=DP_API/feedback_labels'
        + '&get[feedbackToReviewCount]=DP_API/feedback/counts?awaiting_validation%3D1'
        + '&get[active]=DP_API/feedback/counts?status%3Dactive%26group_by%3Dstatus_category'
        + '&get[closed]=DP_API/feedback/counts?status%3Dclosed%26group_by%3Dstatus_category'
        + '&get[hidden]=DP_API/feedback/counts?status%3Dhidden%26group_by%3Dhidden_status'
        + '&get[commentsToReviewCount]=DP_API/feedback_comments/counts?awaiting_validation%3D1'
        + '&get[viewFields]=DP_API/person_setting/feedback_display_fields'
        + '&get[rsTypes]=DP_API/feedback_types'
        + '&get[rsCategories]=DP_API/feedback_categories';

      api.sendGet(batch)
        .success(({ responses }) => {
          const payload    = flattenBatchResponses(responses);
          payload.statuses = { active: payload.active, closed: payload.closed, hidden: payload.hidden };
          if (payload.viewFields && payload.viewFields.hasOwnProperty('value')) {
            dispatch(setDisplayFields({
              visibleFields: {
                card:     payload.viewFields.value.card,
                table:    payload.viewFields.value.table,
                comments: payload.viewFields.value.comments,
                fromDb:   true
              }
            }));
          } else {
            dispatch(setDisplayFields({
              visibleFields: {
                card:     defaultCardFields,
                table:    defaultTableFields,
                comments: defaultCommentTableFields,
                fromDb:   false
              }
            }));
          }
          dispatch(setCollection('FeedbackType', recordStoresId, payload.rsTypes));
          dispatch(setCollection('FeedbackCategory', recordStoresId, payload.rsCategories));
          delete payload.rsTypes;
          delete payload.rsCategories;
          delete payload.active;
          delete payload.closed;
          delete payload.hidden;
          delete payload.viewFields;
          resolve(payload);
        }
      );
    }
  )
);

export const feedbackToValidateCounter = createAction(
  'FEEDBACK_TO_VALIDATE_COUNTER',
  () => repository('Feedback').loadFeedbackToValidate().then(response => response.getData())
);

export const feedbackCustomCategories = createAction(
  'FEEDBACK_CUSTOM_CATEGORIES',
  () => repository('Feedback').loadCustomCategories().then(response => response.getData())
);
