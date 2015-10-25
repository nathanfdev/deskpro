import React, { Component, PropTypes } from 'react';
import { NestedList as BaseNestedList } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';

export class NestedList extends BaseNestedList {
  renderListItem({nested, group, count}, depth) {
    this.ensureValidDepth(depth);
    const label = group[0].toUpperCase() + group.slice(1);

    // 1st level menu items set 'status' filtering option, all other set 'status_category'
    const listOptions = (depth === 1) ? {status: group} : {status_category: group}

    return (
      <ListItemContainer key={group} label={label} count={count} listOptions={listOptions}>
        {this.renderNested(nested, group, depth)}
      </ListItemContainer>
    );
  }
}
