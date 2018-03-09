import React from 'react';
import {
  Drawer,
  Heading,
  ItemList,
  Item,
  Count,
  Icon
} from '@deskpro/react-components';
import PropTypes from 'prop-types';

export default class Stars extends React.Component {
  static propTypes = {
    stars:    PropTypes.array,
    onChange: PropTypes.func,
    opened:   PropTypes.bool,
  };

  close() {
    this.drawer.close();
  }

  render() {
    const {
      stars,
      onChange,
      opened,
    } = this.props;
    return (
      <Drawer
        onChange={onChange}
        opened={opened}
        id="stars"
        ref={(c) => { this.drawer = c; }}
      >
        <Heading>
          My Stars
        </Heading>
        <ItemList>
          {stars.map(star => (
            <Item
              key={star.id}
            >
              <Icon name="star" style={{ color: star.color }} />
              {star.title}
              <Count>0</Count>
            </Item>
            ))}
        </ItemList>
      </Drawer>
    );
  }
}
