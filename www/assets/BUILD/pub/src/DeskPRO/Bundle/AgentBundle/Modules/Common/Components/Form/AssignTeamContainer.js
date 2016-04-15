import React, { Component, PropTypes } from 'react';
import { AssignTeam } from './AssignTeam';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';

import { connect } from 'react-redux';
@connect(state => ({
  me: meSelector(state),
}))

export class AssignTeamContainer extends Component {

  render() {
    return (
      <AssignTeam {...this.props} />
    );
  }
}