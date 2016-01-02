import { createAction } from 'Ampliflux';
import { createRecordsRequest, setRequestRecords } from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const setFeedbackTypesRequest = createAction('SET_FEEDBACK_TYPES_REQUEST', setRequestRecords());