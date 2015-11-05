import Immutable from 'immutable';
import { createSelector } from 'reselect';
import { reduceImmutableToProperty } from 'DeskPRO/Component/Util/Map';
import { filterNamesSelector } from './nav';
import { agentNamesSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { agentTeamNamesSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';
import { departmentNamesSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';
import { languageNamesSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/RecordStores/Selectors/languagesSelectors';
import { RECORD_STORE_REQUEST_ID } from '../Actions/navActions';
import { createPeopleRequestSelectors } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';
import { createOrganizationsRequestSelectors } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/organizationsSelectors';

/**
 * /Components/Nav/Tabs/FiltersTab/ListItemContainer state selector
 *
 * This is specifically created for the ListItemContainer component. Selector provides data structure allowing to
 * determine list item label depending on list grouping and item ID.
 *
 * @param {Immutable} state App state
 * @return {Immutable} Labels grouped by group_by options
 */
export const navItemLabelsSelector = state => Immutable.fromJS({
  filter: filterNamesSelector(state),
  department: departmentNamesSelector(state),
  organization: organizationNamesSelector(state),
  person: personNamesSelector(state),
  language: languageNamesSelector(state),
  agent: agentNamesSelector(state),
  agent_team: agentTeamNamesSelector(state)
});

const peopleSelector = createPeopleRequestSelectors(RECORD_STORE_REQUEST_ID).recordsSel;
const personNamesSelector = createSelector(
  peopleSelector,
  people => reduceImmutableToProperty('name', people)
);

const organizationsSelector = createOrganizationsRequestSelectors(RECORD_STORE_REQUEST_ID).recordsSel;
const organizationNamesSelector = createSelector(
  organizationsSelector,
  organizations => reduceImmutableToProperty('name', organizations)
);
