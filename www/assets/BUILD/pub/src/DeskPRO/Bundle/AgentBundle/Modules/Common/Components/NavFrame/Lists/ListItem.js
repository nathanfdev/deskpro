import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import invariant from 'invariant';
import { pureRender } from 'DeskPRO/Component/Ampliflux';

@pureRender
export class ListItem extends React.Component {

  static propTypes = {
    children:           PropTypes.node,
    count:              PropTypes.any,
    label:              PropTypes.string,
    active:             PropTypes.bool,
    onClick:            PropTypes.func,
    onEdit:             PropTypes.func,
    onItemControlClick: PropTypes.func
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

  onEditClick = event => {
    event.preventDefault();
    this.props.onEdit(event);
  };

  onItemControlClick = event => {
    event.preventDefault();
    if (this.props.onItemControlClick) {
      this.props.onItemControlClick(event);
    }
  };

  renderCountIcon() {
    const { count = 0 } = this.props;

    invariant(!isNaN(parseInt(count, 10)) && isFinite(count), 'The "count" property is not a number');

    return (
      <a className="list-counter active" href="#">{count}</a>
    );
  }

  renderEditButton() {
    return (
      <a href="#" className="edit-icon" onClick={this.onEditClick}>
        <i className="fa fa-cog" />
      </a>
    );
  }

  renderItemControl() {
    const { onItemControlClick } = this.props;

    if (!onItemControlClick) {
      return '';
    }

    return (
      <a href="" className="list-counter-dropdown active" onClick={this.onItemControlClick}>
        <span>&nbsp;</span>
        <i className="fa fa-angle-down" />
      </a>
    );
  }

  render() {
    const { children, active, onClick, onEdit } = this.props;

    let label  = this.props.label;
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
      <li className="counter-display" onMouseEnter={this.onShowEditIcon} onMouseLeave={this.onHideEditIcon}>
        <div className="list-counter-bucket">
          {this.renderItemControl()}
          {this.state.showEditIcon && onEdit ? this.renderEditButton() : this.renderCountIcon()}
        </div>

        <a href="#" className={classNames('item', { active })} onClick={onClick}>
          {label}
        </a>

        {nested}
      </li>
    );
  }
}
