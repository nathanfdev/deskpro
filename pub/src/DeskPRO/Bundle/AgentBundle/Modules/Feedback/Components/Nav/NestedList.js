import React, { Component, PropTypes } from 'react';
import { NestedList as BaseNestedList } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';

export class NestedList extends BaseNestedList {
  renderListItem({nested, group, count}, depth) {
    this.ensureValidDepth(depth);
    const label = group[0].toUpperCase() + group.slice(1);

    return (
      <ListItemContainer key={group} label={label} count={count} listOptions={{'status_category': group}}>
        {this.renderNested(nested, group, depth)}
      </ListItemContainer>
    );
  }
}
