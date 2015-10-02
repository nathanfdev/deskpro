import React from 'react';
import TeamsListItem from './TeamsListItem';

export default class TeamsList extends React.Component {
  render() {
    return (
      <ul className="im-list short">
        {
          this.props.teams.length
            ? this.props.teams.map((team, index) => <TeamsListItem key={index} team={team}/>)
            : null
        }
      </ul>
    );
  }
}