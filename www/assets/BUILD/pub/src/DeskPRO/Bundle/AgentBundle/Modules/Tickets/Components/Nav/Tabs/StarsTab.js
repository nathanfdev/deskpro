import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { ListItemContainer } from './ListItemContainer';
import { SectionHeader, ListItem } from '../../../../Common/Components/NavFrame';

export class StarsTab extends Component {
  static propTypes = {
    starsCount:  PropTypes.object.isRequired,
    onStarClick: PropTypes.func.isRequired
  };

  renderItem = (item, index) =>
    <ListItemContainer
      key={index}
      label={item.get('title')}
      listOptions={{ star: item.get('id') }}
    >
      <ListItem count={item.get('count')} label={item.get('title')} />
    </ListItemContainer>;

  render() {
    const { starsCount } = this.props;

    return (
      <div>
        <SectionHeader>Stars</SectionHeader>
        {starsCount.map((item, index) => this.renderItem(item, index))}
      </div>
    );
  }
}
