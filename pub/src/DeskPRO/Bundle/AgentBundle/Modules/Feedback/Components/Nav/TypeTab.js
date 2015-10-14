import React, { Component, PropTypes } from 'react';
import { FeedbackListItem } from './FeedbackListItem';

export class TypeTab extends Component {
  static propTypes = {
    types: PropTypes.array.isRequired,
    currentGroup: PropTypes.object.isRequired,
    onClick: PropTypes.func.isRequired
  };

  render() {
    const { types } = this.props;

    return (
      <ul>
        {types.map((item, index) =>
          <FeedbackListItem
            key={index}
            count={item.value}
            label={item.title}
            listOptions={{category: item.title}}
          />
        )}
      </ul>
    );
  }
}
