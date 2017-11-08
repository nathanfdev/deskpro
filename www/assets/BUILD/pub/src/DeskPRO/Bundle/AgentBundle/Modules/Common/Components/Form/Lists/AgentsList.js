import PropTypes from 'prop-types';
import React from 'react';
import { AgentsListItem } from './AgentsListItem';
import { EntityList } from './EntityList';

export class AgentsList extends EntityList {

  constructor(props) {
    super(props);
    this.item = AgentsListItem;
  }

  keyword(value) {
    return value.get('name');
  }
}
