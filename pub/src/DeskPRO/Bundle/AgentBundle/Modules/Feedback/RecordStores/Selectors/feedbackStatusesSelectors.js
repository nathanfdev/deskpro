import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function feedbackStatusesStateSel(state) {
  return state.RecordStores.Feedback.feedback.statuses;
}

export const feedbackStatusesStateSelector = createStoreSelectors(feedbackStatusesStateSel);
export const createFeedbackStatusesRequestSelectors = createRequestSelectorsBuilder(feedbackStatusesStateSelector);
