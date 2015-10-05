import { createAction } from 'Ampliflux';
import { requestRecords } from 'Ampliflux/common/record-store/actions';

import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import * as Agents from 'DeskPRO/Bundle/AgentBundle/Services/Api/Agents';

export const loadChats = createAction(
  'IM_LOAD_CHATS',
  requestRecords(
    ['RecordStores', 'chats'],
      missingIds => {
        return new Promise(
          (resolve, reject) =>
            DpApi.sendGet('DP_API/agent_chats?ids=' + missingIds.toArray().join(','))
              .success(response => resolve(response.data))
              .error(response => reject(response))
        );
      }
  )
);