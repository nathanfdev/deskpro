import { createAction } from 'Ampliflux';
import { setRequestRecords } from 'Ampliflux/common/record-store/actions';

export const setDownloadsRequest = createAction('SET_DOWNLOADS_REQUEST', setRequestRecords());
