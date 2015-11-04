import { createAction } from 'Ampliflux';
import { loadAllDepartments } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/departmentsActions';
import { loadAllAgents } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentsActions';
import { loadAllAgentTeams } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentTeamsActions';
import { loadAll as loadAllLanguages } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/RecordStores/Actions/languagesActions';
import { loadAllUserGroups } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/userGroupsActions';
import { loadMyAgentTeams } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentTeamsActions';
import { loadMyDepartments } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/departmentsActions';

export const preloadData = createAction(
  'BOOTSTRAP_PRELOAD_DATA',
  () => dispatch => {
    dispatch(loadAllDepartments());
    dispatch(loadAllAgents());
    dispatch(loadAllAgentTeams());
    dispatch(loadAllLanguages());
    dispatch(loadAllUserGroups());
    dispatch(loadMyAgentTeams());
    dispatch(loadMyDepartments());
  }
);
