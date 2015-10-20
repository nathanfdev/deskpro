import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';

export const releaseProfile = createAction('RELEASE_PROFILE', recordStoreActions.releaseRecords());
export const releaseProfileRequest = createAction('RELEASE_PROFILE_REQUEST', recordStoreActions.releaseRequest());
export const setProfileRequest = createAction('SET_PROFILE', recordStoreActions.setRequestRecords());
