import { createAction } from 'Ampliflux';
import { setRequestRecords } from 'Ampliflux/common/record-store/actions';

export const setFeedbackStatusCategoriesRequest = createAction('SET_FEEDBACK_STATUS_CATEGORIES_REQUEST', setRequestRecords());
