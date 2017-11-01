import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { ListItem } from '../../../Common/Components/NavFrame';
import { ListItemContainer } from './ListItemContainer';

export class TypeTab extends Component {
  static propTypes = {
    types: PropTypes.object.isRequired
  };

  renderType = (item, index) =>
    <ListItemContainer
      key={index}
      label={item.get('title')}
      listOptions={{ isComments: false, navItem: { category: item.get('title') } }}
    >
      <ListItem count={item.get('count')} label={item.get('title')} />
    </ListItemContainer>;

  render() {
    const { types } = this.props;

    return (
      <ul>
        {types && types.get('nested').map((item, index) => this.renderType(item, index))}
      </ul>
    );
  }
}
