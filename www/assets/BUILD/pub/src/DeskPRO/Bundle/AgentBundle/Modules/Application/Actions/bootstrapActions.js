import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import { setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { setAgentSettings } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Actions/settingsActions';
import { setupActionAlerts }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/notificationActions';

export const donePreloading = createAction('APP_BOOTSTRAP_DONE_PRELOADING');
export const preloadData = createAction(
  'BOOTSTRAP_PRELOAD_DATA',
  () => dispatch => new Promise(
    (resolve) => {
      const batch = 'DP_API/batch?get='
          + 'DP_API/ticket_departments'
          + ',DP_API/ticket_departments%3Fmy%3Dtrue'
          + ',DP_API/chat_departments%3Fmy%3Dtrue'
          + ',DP_API/agents'
          + ',DP_API/agent_teams'
          + ',DP_API/agent_teams%3Fmy%3Dtrue'
          + ',DP_API/languages'
          + ',DP_API/user_groups'
          + ',DP_API/helpdesk/agent-client/settings'
          + ',DP_API/notify/setup/action-alerts'
          + ',DP_API/me'
        ;
      api.sendGet(batch)
        .success(({ responses }) => {
          const data = flattenBatchResponses(responses);
          dispatch(setCollection('Department', 'all', data[0]));
          dispatch(setCollection('Department', 'my', data[1]));
          dispatch(setCollection('ChatDepartment', 'my', data[2]));
          dispatch(setCollection('Person', 'agents', data[3]));
          dispatch(setCollection('AgentTeam', 'all', data[4]));
          dispatch(setCollection('AgentTeam', 'my', data[5]));
          dispatch(setCollection('Language', 'all', data[6]));
          dispatch(setCollection('UserGroup', 'all', data[7]));
          dispatch(setAgentSettings(data[8]));
          dispatch(setupActionAlerts(data[9]));
          dispatch(setCollection('Person', 'me', [data[10].person]));

          dispatch(donePreloading());
        })
      ;

      return resolve();
    }
  )
);
