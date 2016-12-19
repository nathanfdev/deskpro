import React, { Component } from 'react';
import { AssignTeam } from './AssignTeam';
import { createSelector } from 'reselect';
import { myAgentTeamsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/common';

import { connect } from 'react-redux';
@connect(state => ({
  team: createSelector(myAgentTeamsSelector, teams => teams.first())(state)
}))

export class AssignTeamContainer extends Component {

  render() {
    return (
      <AssignTeam {...this.props} />
    );
  }
}
