import { createAction } from 'Ampliflux';
import { setRequestRecords } from 'Ampliflux/common/record-store/actions';

export const setDownloadsCommentsRequest = createAction('SET_DOWNLOADS_COMMENTS_REQUEST', setRequestRecords());
