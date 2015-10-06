import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function feedbackTypesStateSel(state) {
  return state.RecordStores.Feedback.feedbackTypes;
}

export const feedbackTypesStateSelector          = createStoreSelectors(feedbackTypesStateSel);
export const createFeedbackTypesRequestSelectors = createRequestSelectorsBuilder(feedbackTypesStateSelector);
