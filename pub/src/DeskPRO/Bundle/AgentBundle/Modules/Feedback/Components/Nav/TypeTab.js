import React, { Component, PropTypes } from 'react';
import { ListItemContainer } from './ListItemContainer';

export class TypeTab extends Component {
  static propTypes = {
    types: PropTypes.array.isRequired
  };

  render() {
    const { types } = this.props;
    console.log('Type tab: ', types.toJS());
    return (
      <ul>
        {types.map((item, index) =>
          <ListItemContainer
            key={index}
            count={item.get('value')}
            label={item.get('title')}
            listOptions={{category: item.get('title')}}
          />
        )}
      </ul>
    );
  }
}
