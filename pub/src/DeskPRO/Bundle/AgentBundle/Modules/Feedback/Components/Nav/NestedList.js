import React from 'react';
import { NestedList as BaseNestedList, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';

export class NestedList extends BaseNestedList {

  renderListItem({nested, group, count}, depth) {
    this.ensureValidDepth(depth);
    const label = group[0].toUpperCase() + group.slice(1);

    // 1st level menu items set 'status' filtering option, all other set 'status_category'
    const listOptions = (depth === 1) ? { navItem: { status: group } } : { navItem: { status_category: group } };
    listOptions.isComments = false;

    return (
      <ListItemContainer key={group}
                         label={label}
                         listOptions={listOptions}>

        <ListItem label={label}
                  count={count}>

          {this.renderNested(nested, group, depth)}
        </ListItem>
      </ListItemContainer>
    );
  }
}
