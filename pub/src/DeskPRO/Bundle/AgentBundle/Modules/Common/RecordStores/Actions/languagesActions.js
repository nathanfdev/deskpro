import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';

export const releaseLanguages = createAction('RELEASE_LANGUAGES', rsa.releaseRecords());
export const releaseLanguagesRequest = createAction('RELEASE_LANGUAGES_REQUEST', rsa.releaseRequest());
export const setLanguagesRequest = createAction('SET_LANGUAGES_REQUEST', rsa.setRequestRecords());
