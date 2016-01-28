import { createAction } from 'Ampliflux';
import { setRequestRecords } from 'Ampliflux/common/record-store/actions';

export const setTicketsRequest = createAction('SET_TICKETS_REQUEST', setRequestRecords());