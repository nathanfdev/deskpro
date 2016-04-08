import React from 'react';
import { NestedList as BaseNestedList, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ListItemContainer } from './ListItemContainer';

export class NestedList extends BaseNestedList {
  getListOptions = (depth, item) => {
    if (depth === 1) {
      return { navItem: { status: item.title } };
    } else if (item.type === 'hidden_status') {
      return { navItem: { hidden_status: item.title } };
    }
    return { navItem: { status_category: item.id } };
  };

  renderListItem = (item, depth) => {
    this.ensureValidDepth(depth);
    const { title, count } = item;
    const label = title[0].toUpperCase() + title.slice(1);
    const listOptions = this.getListOptions(depth, item);
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
