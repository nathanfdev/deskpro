import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function feedbackStateSel(state) {
  return state.RecordStores.feedback.feedback;
}

export const feedbackStateSelector          = createStoreSelectors(feedbackStateSel);
export const createFeedbackRequestSelectors = createRequestSelectorsBuilder(feedbackStateSelector);
