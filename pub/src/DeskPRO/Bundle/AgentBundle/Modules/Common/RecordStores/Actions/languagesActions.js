import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import { createRecordsRequest } from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const releaseLanguages = createAction('RELEASE_LANGUAGES', recordStoreActions.releaseRecords());
export const releaseLanguagesRequest = createAction('RELEASE_LANGUAGES_REQUEST', recordStoreActions.releaseRequest());
export const setLanguagesRequest = createAction('SET_LANGUAGES_REQUEST', recordStoreActions.setRequestRecords());
