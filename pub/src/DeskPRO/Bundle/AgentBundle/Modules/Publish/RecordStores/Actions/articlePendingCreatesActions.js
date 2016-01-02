import { createAction } from 'Ampliflux';
import { setRequestRecords } from 'Ampliflux/common/record-store/actions';

export const setArticlePendingCreatesRequest = createAction('SET_ARTICLE_PENDING_CREATES_REQUEST', setRequestRecords());
