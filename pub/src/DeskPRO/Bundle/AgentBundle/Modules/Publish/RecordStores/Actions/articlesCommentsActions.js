import { createAction } from 'Ampliflux';
import { setRequestRecords } from 'Ampliflux/common/record-store/actions';

export const setArticlesCommentsRequest = createAction('SET_ARTICLES_COMMENTS_REQUEST', setRequestRecords());
