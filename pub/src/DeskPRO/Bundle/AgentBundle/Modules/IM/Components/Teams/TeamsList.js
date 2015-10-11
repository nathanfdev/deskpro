import React, {Component, PropTypes} from 'react';
import { TeamsListItem } from './TeamsListItem';
import { connect } from 'react-redux';

import { loadAllAgentTeams } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentTeamsActions'
import { agentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';

@connect(state => ({
  agentTeams: agentTeamsSelector(state),
  me: state.Application.user
}))
export class TeamsList extends Component {

  static propTypes = {
    agentTeams: PropTypes.object.isRequired
  };

  componentWillMount() {
    this.props.dispatch(loadAllAgentTeams());
  }

  render() {
    return (
      <ul className="im-list short">
        {
          this.props.agentTeams.map((team, index) => {
          return <TeamsListItem
            handleClickParticipant={this.props.handleClickParticipant}
            key={index}
            team={team}
            />
        } ) }
      </ul>
    );
  }
}