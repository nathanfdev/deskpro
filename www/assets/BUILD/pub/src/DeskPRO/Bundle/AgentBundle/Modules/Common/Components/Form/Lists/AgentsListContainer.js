import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { AgentsList } from './AgentsList';

import { connect } from 'react-redux';
@connect(state => ({
  values: agentsSelector(state)
}))

export class AgentsListContainer extends Component {

  render() {
    return (
      <AgentsList {...this.props} />
    );
  }
}
