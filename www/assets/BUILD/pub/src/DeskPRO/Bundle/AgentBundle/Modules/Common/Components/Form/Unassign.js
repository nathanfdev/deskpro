import PropTypes from 'prop-types';
import React from 'react';

export class Unassign extends React.Component {

  static propTypes = {
    onClick: PropTypes.func.isRequired
  };

  render() {
    return (
      <div className="dpw-popup-content-item-unassign-all">
        <a href="#" className="checkbox-link" onClick={this.props.onClick}>
          <span>Unassign All</span>
        </a>
      </div>
    );
  }
}
