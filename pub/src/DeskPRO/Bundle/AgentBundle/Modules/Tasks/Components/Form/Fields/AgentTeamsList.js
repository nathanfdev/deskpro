import React, { PropTypes } from 'react';
import { CheckboxList } from './CheckboxList';
import { BaseList } from './BaseList';

export class AgentTeamsList extends BaseList {

  renderLabel(value) {
    return (
      value.get('name')
    );
  }

}
