import React from 'react';
import classNames from 'classnames';
import {
  Drawer,
  Heading,
  Tag,
  List,
  Item,
  Scrollbar
} from '@deskpro/react-components';
import PropTypes from 'prop-types';

class LabelTitle extends React.Component {
  static propTypes = {
    children: PropTypes.node,
  };
  render() {
    const { children, ...props } = this.props;
    return (
      <div style={{ minWidth: '8px' }} {...props}>
        {children}
      </div>
    );
  }
}

export default class Labels extends React.Component {
  static propTypes = {
    labels:       PropTypes.array,
    onChange:     PropTypes.func,
    onSelectMode: PropTypes.func,
    opened:       PropTypes.bool,
    mode:         PropTypes.object,
  };

  close() {
    this.drawer.close();
  }

  sortLabels = () => {
    const groups = {};
    for (const label of this.props.labels) {
      const initial = label.charAt(0).toUpperCase();
      if (!groups[initial]) {
        groups[initial] = [];
      }
      groups[initial].push(label.replace(/ /, ' '));
    }
    Object.keys(groups).forEach((key) => {
      groups[key].sort();
    });
    return groups;
  };

  render() {
    const {
      onChange,
      onSelectMode,
      opened,
      mode,
    } = this.props;

    const groups = this.sortLabels();

    return (
      <Drawer
        onChange={onChange}
        opened={opened}
        id="labels"
        ref={(c) => { this.drawer = c; }}
      >
        <Heading>
          Labels
        </Heading>
        <Scrollbar style={{ height: '300px' }}>
          <List className="dp-drawer-item-list dp-labels">
            {Object.keys(groups).map(key =>
              <Item key={key} leftTypes={[LabelTitle]}>
                <LabelTitle>{key}</LabelTitle>
                <div className="dp-label-list">
                  {groups[key].map(label =>
                    [
                      <Tag
                        key={label}
                        className={classNames('dp-label', { enabled: mode && label === mode.label })}
                        onClick={() => onSelectMode({ type: 'label', label })}
                      >
                        {label}
                      </Tag>,
                      ' ']
                  )}
                </div>
              </Item>
            )}
          </List>
        </Scrollbar>
      </Drawer>
    );
  }
}
