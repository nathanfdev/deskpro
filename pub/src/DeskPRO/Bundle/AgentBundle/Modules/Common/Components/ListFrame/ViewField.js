import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';

export class ViewField extends Component {
  /**
   * The valid PropTypes for this component
   * @type {Object}
   */
  static propTypes = {
    fixed: PropTypes.bool,
    label: PropTypes.string.isRequired
  };

  render() {
    const {label, fixed } = this.props;
    const moveIconClass = classNames('fa', {'fa-minus': fixed, 'fa-navicon': !fixed});
    return (
      <a className="dpw-navigation-dropdown-column-list-item" href="#">
        <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
        <span className="dpw-navigation-dropdown-column-list-move">
          <i className={moveIconClass}></i>
        </span>
        <span className="dpw-navigation-dropdown-column-list-title">{label}</span>
      </a>
    );
  }
}
