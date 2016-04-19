import React, { Component } from 'react';
import { AssignAgent } from './AssignAgent';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';

import { connect } from 'react-redux';
@connect(state => ({
  me: meSelector(state)
}))

export class AssignAgentContainer extends Component {

  render() {
    return (
      <AssignAgent {...this.props} />
    );
  }
}