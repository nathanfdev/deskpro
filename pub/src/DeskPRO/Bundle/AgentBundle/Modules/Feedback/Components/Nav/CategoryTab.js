import React, { Component, PropTypes } from 'react';
import { ListItemContainer } from './ListItemContainer';

export class CategoryTab extends Component {

  static propTypes = {
    customCategories: PropTypes.object.isRequired
  };

  render() {
    const { customCategories } = this.props;

    return (
      <ul>
        {customCategories.toJS().map((item, index) =>
            <ListItemContainer
              key={index}
              count={item.count}
              label={item.group}
              listOptions={{navItem: {custom_category: item.group}}}
              />
        )}
      </ul>
    );
  }
}