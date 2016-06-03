import React from 'react';
import { constants } from '../../../../Constants/Constants';
import { NestedList as BaseNestedList, ListItem } from '../../../Common/Components/NavFrame';
import { ListItemContainer } from './ListItemContainer';

export class NestedList extends BaseNestedList {

  renderListItem(item, depth, key) {
    this.ensureValidDepth(depth);

    const { isAgent, group } = this.props;

    const { title, count, type, id } = item;
    const label = title ? title[0].toUpperCase() + title.slice(1) : '-';
    const listOptions = {
      content:   'people',
      is_agent:  isAgent,
      order_by:  'name',
      order_dir: constants.ORDER_ASC
    };

    if (type) {
      listOptions.navItem = { [type]: id };
    }

    return (
      <ListItemContainer key={`${depth}-${key}`} group={group} label={label} listOptions={listOptions}>
        <ListItem label={label} count={count}>
          {this.renderNested(item, depth)}
        </ListItem>
      </ListItemContainer>
    );
  }
}
