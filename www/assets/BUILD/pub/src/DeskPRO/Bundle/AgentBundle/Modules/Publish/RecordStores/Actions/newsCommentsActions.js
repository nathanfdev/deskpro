import { createAction } from 'Ampliflux';
import { setRequestRecords } from 'Ampliflux/common/record-store/actions';

export const setNewsCommentsRequest = createAction('SET_NEWS_COMMENTS_REQUEST', setRequestRecords());
