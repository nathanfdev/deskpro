import { createAction } from 'Ampliflux';
import { setRequestRecords } from 'Ampliflux/common/record-store/actions';

export const setFeedbackTypesRequest = createAction('SET_FEEDBACK_TYPES_REQUEST', setRequestRecords());
