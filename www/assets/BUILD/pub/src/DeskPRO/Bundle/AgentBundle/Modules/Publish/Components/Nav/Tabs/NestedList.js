import React from 'react';
import { NestedList as BaseNestedList, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ListItemContainer } from './ListItemContainer';

export class NestedList extends BaseNestedList {
  renderListItem(item, depth, key) {
    const { content, groupedBy } = this.props;
    this.ensureValidDepth(depth);
    const { title, count, id, type } = item;
    const label       = title[0].toUpperCase() + title.slice(1);
    const listOptions = { content, navItem: { [type]: id } };

    return (
      <ListItemContainer
        key={`${depth}-${key}`}
        label={label}
        group={groupedBy}
        listOptions={listOptions}
        content={content}
      >
        <ListItem label={label} count={count}>
          {this.renderNested(item, depth)}
        </ListItem>
      </ListItemContainer>
    );
  }
}
