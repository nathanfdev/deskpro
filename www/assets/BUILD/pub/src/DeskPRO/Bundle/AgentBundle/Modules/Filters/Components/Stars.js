import React from 'react';
import {
  Drawer,
  Heading,
  ItemList,
  Item,
  Count,
  Icon
} from '@deskpro/react-components';
import { faStar } from '@fortawesome/free-regular-svg-icons';
import PropTypes from 'prop-types';

export default class Stars extends React.Component {
  static propTypes = {
    stars:        PropTypes.object,
    starsCounts:  PropTypes.object,
    onChange:     PropTypes.func,
    onSelectMode: PropTypes.func,
    opened:       PropTypes.bool,
    mode:         PropTypes.object,
  };

  onSelect(selected, star) {
    this.props.onSelectMode({ type: 'star', star: star.get('id') });
  }

  close() {
    this.drawer.close();
  }

  renderCount(star) {
    const count = this.props.starsCounts.find(e => e.get('id') === star.get('id'));
    if (count) {
      return <Count>{count.get('count')}</Count>;
    }
    return <Count>&middot;</Count>;
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
        <ItemList
          controlled
        >
          {stars.toArray().map(star => (
            <Item
              key={star.get('id')}
              selected={mode && mode.type === 'star' && mode.star === star.get('id')}
              onClick={selected => this.onSelect(selected, star)}
            >
              <Icon name={faStar} style={{ color: star.get('hex') }} />
              {star.get('name')}
              {this.renderCount(star)}
            </Item>
            ))}
        </ItemList>
      </Drawer>
    );
  }
}
