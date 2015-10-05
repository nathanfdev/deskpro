import React, {Component, PropTypes} from 'react';
import { TeamsListItem } from './TeamsListItem';
import { connect } from 'react-redux';

import { loadAllAgentTeams } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentTeamsActions'
import { agentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';

@connect(state => ({
  agentTeams: agentTeamsSelector(state)
}))
export class TeamsList extends Component {

  static propTypes = {
    agentTeams: PropTypes.object.isRequired
  };

  componentWillMount() {
    "use strict";
    this.props.dispatch(loadAllAgentTeams());
  }

  render() {
    return (
      <ul className="im-list short">
        { this.props.agentTeams.map((team, index) => <TeamsListItem key={index} team={team}/> ) }
      </ul>
    );
  }
}