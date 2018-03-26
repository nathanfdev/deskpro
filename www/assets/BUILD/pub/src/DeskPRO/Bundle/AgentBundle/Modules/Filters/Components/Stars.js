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
    stars:        PropTypes.array,
    onChange:     PropTypes.func,
    onSelectMode: PropTypes.func,
    opened:       PropTypes.bool,
    mode:         PropTypes.object,
  };

  onSelect(selected, star) {
    this.props.onSelectMode({ type: 'star', star: star.id });
  }

  close() {
    this.drawer.close();
  }

  render() {
    const {
      stars,
      onChange,
      opened,
      mode,
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
              selected={mode && mode.type === 'star' && mode.star === star.id}
              onSelect={selected => this.onSelect(selected, star)}
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
