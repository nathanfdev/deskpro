import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function feedbackCommentsStateSel(state) {
  return state.RecordStores.Feedback.feedback.comments;
}

export const feedbackCommentsStateSelector          = createStoreSelectors(feedbackCommentsStateSel);
export const createFeedbackCommentsRequestSelectors = createRequestSelectorsBuilder(feedbackCommentsStateSelector);
