import { createAction } from 'Ampliflux';
import { setRequestRecords } from 'Ampliflux/common/record-store/actions';

export const setNewsRequest = createAction('SET_NEWS_REQUEST', setRequestRecords());
