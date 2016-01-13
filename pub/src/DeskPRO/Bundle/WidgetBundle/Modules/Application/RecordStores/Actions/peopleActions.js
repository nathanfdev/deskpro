import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';

export const releasePeople = createAction('WIDGET_RELEASE_PEOPLE', rsa.releaseRecords());
export const releasePeopleRequest = createAction('WIDGET_RELEASE_PEOPLE_REQUEST', rsa.releaseRequest());
export const setPeopleRequest = createAction('WIDGET_SET_PEOPLE_REQUEST', rsa.setRequestRecords());


