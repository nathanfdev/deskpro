import { createAction } from 'Ampliflux';
import { setRequestRecords } from 'Ampliflux/common/record-store/actions';

export const setArticlesRequest = createAction('SET_ARTICLES_REQUEST', setRequestRecords());
