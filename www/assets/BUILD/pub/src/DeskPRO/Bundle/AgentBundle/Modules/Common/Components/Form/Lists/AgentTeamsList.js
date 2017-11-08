import PropTypes from 'prop-types';
import React from 'react';
import { AgentTeamsListItem } from './AgentTeamsListItem';
import { EntityList } from './EntityList';

export class AgentTeamsList extends EntityList {

  constructor(props) {
    super(props);
    this.item = AgentTeamsListItem;
  }

  keyword(value) {
    return value.get('name');
  }
}
