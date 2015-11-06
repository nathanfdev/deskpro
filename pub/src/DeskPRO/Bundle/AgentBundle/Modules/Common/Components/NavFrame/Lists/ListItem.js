import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { pureRender } from 'Ampliflux';
import { BaseList } from './BaseList';

@pureRender
export class ListItem extends BaseList {

  static propTypes = {
    children: PropTypes.node,
    count: PropTypes.number.isRequired,
    label: PropTypes.string,
    active: PropTypes.bool.isRequired,
    onClick: PropTypes.func.isRequired
  };

  render() {
    const { children, count, active, onClick } = this.props;
    const classes = classNames('item', { 'active': active });

    let label = this.props.label;
    let nested = '';

    if (children instanceof Array && children.length) {
      children.forEach(child => {
        if (child.props.part === 'label') {
          label = child;
        } else if (child.props.part === 'nested') {
          nested = child;
        }
      });
    } else if (children instanceof Object && children.props.part === 'label') {
      label = children;
    } else {
      nested = children;
    }

    return (
      <li className="counter-display">
        {this.renderCount(count)}
        <a href="#" className={classes} onClick={onClick}>
          {label}
        </a>

        {nested}
      </li>
    );
  }
}
