import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import { setDepartmentsRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/departmentsActions';
import { setPeopleRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';
import { setAgentTeamsRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentTeamsActions';
import { setLanguagesRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/RecordStores/Actions/languagesActions';
import { setUserGroupsRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/userGroupsActions';
import { setAgentSettings } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Actions/settingsActions';
import { setupActionAlerts } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/notificationActions';

export const donePreloading = createAction('APP_BOOTSTRAP_DONE_PRELOADING');
export const preloadData = createAction(
  'BOOTSTRAP_PRELOAD_DATA',
  () => dispatch => new Promise(
    (resolve) => {
      const batch = 'DP_API/batch?get='
        + 'DP_API/ticket_departments'
        + ',DP_API/ticket_departments%3Fmy%3Dtrue'
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
        .success(({responses}) => {
          const data = flattenBatchResponses(responses);
          dispatch(setDepartmentsRequest('all', data[0]));
          dispatch(setDepartmentsRequest('my', data[1]));
          dispatch(setPeopleRequest('agents', data[2]));
          dispatch(setAgentTeamsRequest('all', data[3]));
          dispatch(setAgentTeamsRequest('my', data[4]));
          dispatch(setLanguagesRequest('all', data[5]));
          dispatch(setUserGroupsRequest('all', data[6]));
          dispatch(setAgentSettings(data[7]));
          dispatch(setupActionAlerts(data[8]));
          dispatch(setPeopleRequest('me', [data[9].person]));

          dispatch(donePreloading());
        })
      ;

      return resolve();
    }
  )
);
