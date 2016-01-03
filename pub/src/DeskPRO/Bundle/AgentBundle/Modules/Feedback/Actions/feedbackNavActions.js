import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import * as Feedback from 'DeskPRO/Bundle/AgentBundle/Services/Api/Feedback';
import { setFeedbackTypesRequest } from '../RecordStores/Actions/feedbackTypesActions';
import { setFeedbackCategoriesRequest } from '../RecordStores/Actions/feedbackCategoriesActions';
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
            + '&get[toValidateCount]=DP_API/feedback/counts?awaiting_validation%3D1'
            + '&get[active]=DP_API/feedback/counts?status%3Dactive%26group_by%3Dstatus_category'
            + '&get[closed]=DP_API/feedback/counts?status%3Dclosed%26group_by%3Dstatus_category'
            + '&get[hidden]=DP_API/feedback/counts?status%3Dhidden%26group_by%3Dhidden_status'
            + '&get[commentsToReviewCount]=DP_API/feedback_comments/counts?awaiting_validation%3D1'
            + '&get[viewFields]=DP_API/person_setting/feedback_display_fields'
            + '&get[rsTypes]=DP_API/feedback_types'
            + '&get[rsCategories]=DP_API/feedback_categories'
          ;
        DpApi.sendGet(batch)
          .success(({responses}) => {
            const payload = flattenBatchResponses(responses);
            payload.statuses = { active: payload.active, closed: payload.closed, hidden: payload.hidden };
            if (payload.viewFields && payload.viewFields.hasOwnProperty('value')) {
              dispatch(setDisplayFields({
                cardVisibleFields: payload.viewFields.value.cardVisibleFields,
                tableVisibleFields: payload.viewFields.value.tableVisibleFields,
                viewFieldsSettingsFromDb: true
              }));
            } else {
              dispatch(setDisplayFields({
                cardVisibleFields: defaultCardFields,
                tableVisibleFields: defaultTableFields,
                commentsTableVisibleFields: defaultCommentTableFields,
                viewFieldsSettingsFromDb: false
              }));
            }
            dispatch(setFeedbackTypesRequest(recordStoresId, payload.rsTypes));
            dispatch(setFeedbackCategoriesRequest(recordStoresId, payload.rsCategories));
            delete payload.rsTypes;
            delete payload.rsCategories;
            delete payload.active;
            delete payload.closed;
            delete payload.hidden;
            delete payload.viewFields;
            resolve(payload);
          }
        )
        ;
      }
    )
  )
  ;

export const feedbackToValidateCounter = createAction(
  'FEEDBACK_TO_VALIDATE_COUNTER',
  () => Feedback.feedbackToValidate().then(promise => promise.getData())
);

export const feedbackCustomCategories = createAction(
  'FEEDBACK_CUSTOM_CATEGORIES',
  () => Feedback.getCustomCategories().then(promise => promise.getData())
);
