import React, { Component, PropTypes } from 'react';
import { FeedbackListItem } from './FeedbackListItem';

export class CategoryTab extends Component {

  static propTypes = {
    customCategories: PropTypes.array.isRequired
  };

  render() {
    const { customCategories } = this.props;

    return (
      <ul>
        {customCategories.map((item, index) =>
          <FeedbackListItem
            key={index}
            count={item.count}
            label={item.group}
            listOptions={{custom_category: item.group}}
          />
        )}
      </ul>
    );
  }
}