import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { AgentTeamsList } from './AgentTeamsList';

import { connect } from 'react-redux';
@connect(state => ({
  values: allSelectorFactory('AgentTeam')(state)
}))

export class AgentTeamsListContainer extends Component {

  render() {
    return (
      <AgentTeamsList {...this.props} />
    );
  }
}
