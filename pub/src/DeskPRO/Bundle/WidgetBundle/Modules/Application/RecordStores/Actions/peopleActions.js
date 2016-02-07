import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';
import { widgetApi } from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { ajaxOptions } from '../../Actions/bootstrapActions';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';

export const releasePeople = createAction('WIDGET_RELEASE_PEOPLE', rsa.releaseRecords());
export const releasePeopleRequest = createAction('WIDGET_RELEASE_PEOPLE_REQUEST', rsa.releaseRequest());
export const setPeopleRequest = createAction('WIDGET_SET_PEOPLE_REQUEST', rsa.setRequestRecords());

export const loadPeople = createAction(
  'WIDGET_LOAD_PEOPLE',
  rsa.requestRecords(
    ['RecordStores', 'Application', 'people'],
    missingIds => new Promise((resolve, reject) =>
      widgetApi.sendGet('DP_API/people?' + compileParams({ids: missingIds.toArray()}), {...ajaxOptions})
        .success(response => resolve(response.data))
        .error(response => reject(response))
    )
  )
);
