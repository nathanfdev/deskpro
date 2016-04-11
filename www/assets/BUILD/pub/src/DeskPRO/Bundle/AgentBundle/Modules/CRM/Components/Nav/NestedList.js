import React from 'react';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { NestedList as BaseNestedList, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ListItemContainer } from './ListItemContainer';

export class NestedList extends BaseNestedList {
  renderListItem(item, depth) {
    this.ensureValidDepth(depth);

    const { isAgent, group } = this.props;
    const { title, count, type, id } = item;
    const label       = title ? title[0].toUpperCase() + title.slice(1) : '-';
    const listOptions = { content: 'people', is_agent: isAgent, order_by: 'name', order_dir: constants.ORDER_ASC };

    if (type) {
      listOptions.navItem = { [type]: id };
    }

    return (
      <ListItemContainer key={title}
                         group={group}
                         label={label}
                         listOptions={listOptions}>
        <ListItem label={label} count={count}>
          {this.renderNested(item, depth)}
        </ListItem>
      </ListItemContainer>
    );
  }
}
