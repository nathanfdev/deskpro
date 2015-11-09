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
    onClick: PropTypes.func.isRequired,
    onEdit: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      showEditIcon: false
    };
  }

  onShowEditIcon = () => {
    this.setState({
      showEditIcon: true
    });
  };

  onHideEditIcon = () => {
    this.setState({
      showEditIcon: false
    });
  };

  render() {
    const { children, count, active, onClick, onEdit } = this.props;

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
        <div onMouseEnter={this.onShowEditIcon}
             onMouseLeave={this.onHideEditIcon}>

          {this.state.showEditIcon && onEdit ? this.renderEditButton() : this.renderCount(count)}
        </div>

        <a href="#"
           className={classNames('item', { 'active': active })}
           onClick={onClick}>

          {label}
        </a>

        {nested}
      </li>
    );
  }
}
