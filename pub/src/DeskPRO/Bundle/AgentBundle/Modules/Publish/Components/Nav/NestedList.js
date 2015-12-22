import React from 'react';
import { NestedList as BaseNestedList, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';

export class NestedList extends BaseNestedList {
  renderListItem(item, depth) {
    const {content} = this.props;
    this.ensureValidDepth(depth);
    const { title, count, id, type } = item;
    const label = title[0].toUpperCase() + title.slice(1);
    const listOptions = { content: content, [type]: id };

    return (
      <ListItemContainer key={title}
                         label={label}
                         group={content}
                         listOptions={listOptions}>

        <ListItem label={label}
                  count={count}>
          {this.renderNested(item, depth)}
        </ListItem>
      </ListItemContainer>
    );
  }
}
