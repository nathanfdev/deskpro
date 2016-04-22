import React, { PropTypes } from 'react';
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
