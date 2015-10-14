import React, { Component, PropTypes } from 'react';
import { FeedbackListItem } from './FeedbackListItem';

export class CategoryTab extends Component {

  static propTypes = {
    customCategories: PropTypes.array.isRequired,
    currentGroup: PropTypes.object.isRequired,
    onClick: PropTypes.func.isRequired
  };

  render() {
    const { customCategories, onClick, currentGroup } = this.props;

    return (
      <ul>
        {customCategories.map((item, index) =>
          <FeedbackListItem
            key={index}
            itemId={item.group}
            count={item.count}
            label={item.group}
            onClick={onClick({name: 'custom_category', value: item.group})}
            active={currentGroup.name === 'custom_category' && currentGroup.value === item.group}
          />
        )}
      </ul>
    );
  }
}