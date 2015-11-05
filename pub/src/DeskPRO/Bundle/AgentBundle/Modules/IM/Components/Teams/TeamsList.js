import React, {Component, PropTypes} from 'react';
import { TeamsListItem } from './TeamsListItem';
import { connect } from 'react-redux';

// teams
import { myAgentTeamsSelector, myAgentTeamsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';

@connect(state => ({
  teams: myAgentTeamsSelector(state),
  teamsStatus: myAgentTeamsStatusSelector(state)
}))
export class TeamsList extends Component {

  static propTypes = {
    teams: PropTypes.object.isRequired,
    teamsStatus: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  render() {
    return (
      <ul className="im-list short">
        {
          this.props.teams.map((team, index) => {
            return (<TeamsListItem
              dispatch={this.props.dispatch}
              key={index}
              team={team}
              />);
          }) }
      </ul>
    );
  }
}