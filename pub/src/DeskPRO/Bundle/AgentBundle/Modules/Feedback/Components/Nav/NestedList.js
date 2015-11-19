import React from 'react';
import { NestedList as BaseNestedList, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';

export class NestedList extends BaseNestedList {
  getListOptions(depth, parent, group) {
    if (depth === 1) {
      return { navItem: { status: group } };
    } else if (parent === 'hidden') {
      return { navItem: { hidden_status: group } };
    }
    return { navItem: { status_category: group } };
  }

  renderListItem(item, depth) {
    this.ensureValidDepth(depth);
    const { group, count, parent } = item;
    const label = group[0].toUpperCase() + group.slice(1);

    const listOptions = this.getListOptions(depth, parent, group);
    listOptions.isComments = false;

    return (
      <ListItemContainer key={group}
                         label={label}
                         listOptions={listOptions}>

        <ListItem label={label}
                  count={count}>
          {this.renderNested(item, depth)}
        </ListItem>
      </ListItemContainer>
    );
  }
}
