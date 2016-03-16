import React, { PropTypes } from 'react';
import { BaseList } from './BaseList';

export class AgentTeamsList extends BaseList {

  renderLabel(value) {
    return (
      value.get('name')
    );
  }

  getKeyword(value) {
    return value.get('name');
  }
}
