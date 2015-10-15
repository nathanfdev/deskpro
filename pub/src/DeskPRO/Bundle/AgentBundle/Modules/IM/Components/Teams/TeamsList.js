import React, {Component, PropTypes} from 'react';
import { TeamsListItem } from './TeamsListItem';

export class TeamsList extends Component {

  static propTypes = {
    agentTeams: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    handleClickParticipant: PropTypes.func.isRequired
  };

  render() {
    return (
      <ul className="im-list short">
        {
          this.props.agentTeams.map((team, index) => {
            return (<TeamsListItem
              dispatch={this.props.dispatch}
              handleClickParticipant={this.props.handleClickParticipant}
              key={index}
              team={team}
              />);
          }) }
      </ul>
    );
  }
}