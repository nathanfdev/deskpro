import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { pureRender } from 'Ampliflux';

@pureRender
export class ListItem extends React.Component {

  static propTypes = {
    children: PropTypes.node,
    count: PropTypes.any,
    label: PropTypes.string,
    active: PropTypes.bool,
    onClick: PropTypes.func,
    onEdit: PropTypes.func,
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

  renderEditButton() {
    const onClick = event => {
      event.preventDefault();
      this.props.onEdit(event);
    };

    return (
      <a href="#" className="edit-icon" onClick={onClick}>
        <i className="fa fa-cog" />
      </a>
    );
  }

  renderCountIcon() {
    const { count = 0 } = this.props;

    return (
      <a className="list-counter active" href="#">{Number.isInteger(count) ? count : 0}</a>
    );
  }

  renderItemControl() {
    const { onItemControlClick } = this.props;

    if (!onItemControlClick) {
      return '';
    }

    const onClick = event => {
      event.preventDefault();
      onItemControlClick(event);
    };

    return (
      <a href="" className="list-counter-dropdown active" onClick={onClick}>
        <span>&nbsp;</span>
        <i className="fa fa-angle-down"></i>
      </a>
    );
  }

  render() {
    const { children, active, onClick, onEdit } = this.props;

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
        <div className="list-counter-bucket"
             onMouseEnter={this.onShowEditIcon}
             onMouseLeave={this.onHideEditIcon}>

          {this.renderItemControl()}
          {this.state.showEditIcon && onEdit ? this.renderEditButton() : this.renderCountIcon()}
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
