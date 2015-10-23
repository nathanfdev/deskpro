import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function feedbackCategoriesStateSel(state) {
  return state.RecordStores.Feedback.feedback.categories;
}

export const feedbackCategoriesStateSelector = createStoreSelectors(feedbackCategoriesStateSel);
export const createFeedbackCategoriesRequestSelectors = createRequestSelectorsBuilder(feedbackCategoriesStateSelector);
