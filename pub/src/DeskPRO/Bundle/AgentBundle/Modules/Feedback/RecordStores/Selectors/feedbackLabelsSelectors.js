import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function feedbackLabelsStateSel(state) {
  return state.RecordStores.Feedback.feedbackLabels;
}

export const feedbackLabelsStateSelector = createStoreSelectors(feedbackLabelsStateSel);
export const createFeedbackLabelsRequestSelectors = createRequestSelectorsBuilder(feedbackLabelsStateSelector);
