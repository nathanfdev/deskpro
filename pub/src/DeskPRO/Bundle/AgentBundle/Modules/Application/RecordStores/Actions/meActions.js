import { createAction } from 'Ampliflux';
import { createRecordsRequest } from 'Ampliflux/common/record-store/actions';
import { loadPeople } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const loadMe = createAction(
  loadPeople.type,
  createRecordsRequest(
    ['RecordStores', 'CRM', 'people'],
    'me',
    () => new Promise((resolve, reject) =>
      DpApi.sendGet('DP_API/me')
        .success(response => resolve([response.data.person]))
        .error((data, response) => reject(response))
    )
  )
);
