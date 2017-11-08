import PropTypes from 'prop-types';
import React, { Component } from 'react';
import classNames from 'classnames';

export class ViewField extends Component {

  static propTypes = {
    fixed:             PropTypes.bool,
    index:             PropTypes.number,
    field:             PropTypes.object.isRequired,
    connectDragSource: PropTypes.func.isRequired,
    connectDropTarget: PropTypes.func.isRequired,
    isDragging:        PropTypes.bool.isRequired,
    toggleVisibility:  PropTypes.func
  };

  clickHandle = (event) => {
    event.preventDefault();
    const { toggleVisibility, index } = this.props;
    if (!toggleVisibility) return;
    toggleVisibility(index);
  };

  renderStatus() {
    if (!this.props.field.get('visible')) {
      return null;
    }

    return (
      <span className="dpw-navigation-dropdown-column-list-status">
        <i className="fa fa-check" />
      </span>
    );
  }

  render() {
    const { isDragging, connectDragSource, connectDropTarget, field, fixed } = this.props;
    const anchorClasses = classNames('dpw-navigation-dropdown-column-list-item', {
      'dpw-navigation-dropdown-item-disabled': fixed
    });
    const moveIconClass = classNames('fa', {
      'fa-minus':   fixed,
      'fa-navicon': !fixed
    });

    return connectDragSource(connectDropTarget(
      <li className={classNames({ 'dragging-item': isDragging })} onClick={this.clickHandle}>
        <a className={anchorClasses}>
          {this.renderStatus()}
          <span className="dpw-navigation-dropdown-column-list-move">
            <i className={moveIconClass} />
          </span>
          <span className="dpw-navigation-dropdown-column-list-title">{field.get('title')}</span>
        </a>
      </li>
    ));
  }
}
