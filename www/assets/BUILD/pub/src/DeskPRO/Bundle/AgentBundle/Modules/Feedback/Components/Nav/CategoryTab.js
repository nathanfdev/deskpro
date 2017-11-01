import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { ListItem } from '../../../Common/Components/NavFrame';
import { ListItemContainer } from './ListItemContainer';

export class CategoryTab extends Component {

  static propTypes = {
    categories: PropTypes.object.isRequired
  };

  renderCategory = (item, index) =>
    <ListItemContainer
      key={index}
      label={item.get('title')}
      listOptions={{ isComments: false, navItem: { custom_category: item.get('title') } }}
    >
      <ListItem count={item.get('count')} label={item.get('title')} />
    </ListItemContainer>;

  render() {
    const { categories } = this.props;

    return (
      <ul>
        {categories && categories.get('nested').map((item, index) => this.renderCategory(item, index))}
      </ul>
    );
  }
}
