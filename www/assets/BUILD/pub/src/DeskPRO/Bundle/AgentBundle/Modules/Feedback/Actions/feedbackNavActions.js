import { createAction } from 'DeskPRO/Component/Ampliflux';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import { repository, api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { setDisplayFields } from './FeedbackListActions';
import {
  defaultCardFields, defaultTableFields, defaultCommentTableFields
}
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
      const batchComponents = {
        labels:                { endpoint: 'feedback_labels' },
        rsTypes:               { endpoint: 'feedback_types' },
        rsCategories:          { endpoint: 'feedback_categories' },
        viewFields:            { endpoint: 'person_setting/feedback_display_fields' },
        categories:            { endpoint: 'feedback/counts', query: 'group_by=custom_category' },
        types:                 { endpoint: 'feedback/counts', query: 'group_by=category' },
        active:                { endpoint: 'feedback/counts', query: 'status=active&group_by=status_category' },
        closed:                { endpoint: 'feedback/counts', query: 'status=closed&group_by=status_category' },
        hidden:                { endpoint: 'feedback/counts', query: 'status=hidden&group_by=hidden_status' },
        feedbackToReviewCount: { endpoint: 'feedback/counts', query: 'awaiting_validation=1' },
        commentsToReviewCount: { endpoint: 'feedback_comments/counts', query: 'awaiting_validation=1' }
      };

      const batch = api.prepareParams(batchComponents);

      api.sendGet(batch).success(
        ({ responses }) => {
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
