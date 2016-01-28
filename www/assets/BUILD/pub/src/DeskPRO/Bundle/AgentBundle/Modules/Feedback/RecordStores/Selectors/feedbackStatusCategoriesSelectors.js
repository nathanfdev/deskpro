import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function feedbackStatusCategoriesStateSel(state) {
  return state.RecordStores.Feedback.statusCategories;
}

export const feedbackStatusCategoriesStateSelector = createStoreSelectors(feedbackStatusCategoriesStateSel);
export const createFeedbackStatusCategoriesRequestSelectors = createRequestSelectorsBuilder(feedbackStatusCategoriesStateSelector);
