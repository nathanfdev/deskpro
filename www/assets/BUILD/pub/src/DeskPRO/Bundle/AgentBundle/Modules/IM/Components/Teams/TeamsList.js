import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { TeamsListItem } from './TeamsListItem';
import { connect } from 'react-redux';
import { myAgentTeamsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

@connect(state => ({
  teams: myAgentTeamsSelector(state)
}))
export class TeamsList extends Component {
  static propTypes = {
    teams:    PropTypes.object.isRequired,
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
          })}
      </ul>
    );
  }
}
