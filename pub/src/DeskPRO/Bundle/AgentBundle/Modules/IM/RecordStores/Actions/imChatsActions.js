import { createAction } from 'Ampliflux';
import { requestRecords } from 'Ampliflux/common/record-store/actions';

import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import * as Agents from 'DeskPRO/Bundle/AgentBundle/Services/Api/Agents';

export const loadChats = createAction(
  'IM_CHAT_LOAD_CHATS',
  requestRecords(
    ['RecordStores', 'chats', 'chats'],
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

export const loadRecentAgents = createAction(
  'IM_CHAT_LOAD_RECENT_AGENTS',
  requestRecords(
    ['RecordStores', 'chats', 'recentAgents'],
    (agents) => Agents.loadAgents({ids: agents.join(',')})
  )
);