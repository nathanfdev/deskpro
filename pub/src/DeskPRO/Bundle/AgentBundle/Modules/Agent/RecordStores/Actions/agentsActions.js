import { createAction } from 'Ampliflux';
import { createRecordsRequest } from 'Ampliflux/common/record-store/actions';
import { loadPeople } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const loadAllAgents = createAction(
  loadPeople.type,
  createRecordsRequest(
    ['RecordStores', 'CRM', 'people'],
    'agents',
    () => new Promise((resolve, reject) =>
      DpApi.sendGet('DP_API/agents')
           .success(response => resolve(response.data))
           .error(response => reject(response))),
    'append'
  )
);
