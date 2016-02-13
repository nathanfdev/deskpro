import React from 'react';
import { NestedList as BaseNestedList, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
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
    const { title, count, parent, id } = item;
    const label = title[0].toUpperCase() + title.slice(1);
    const listOptions = this.getListOptions(depth, parent, id);
    listOptions.isComments = false;

    return (
      <ListItemContainer key={title}
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
