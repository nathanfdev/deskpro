import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function feedbackCommentsStateSel(state) {
  return state.RecordStores.Feedback.comments;
}

export const feedbackCommentsStateSelector = createStoreSelectors(feedbackCommentsStateSel);
export const createFeedbackCommentsRequestSelectors = createRequestSelectorsBuilder(feedbackCommentsStateSelector);
