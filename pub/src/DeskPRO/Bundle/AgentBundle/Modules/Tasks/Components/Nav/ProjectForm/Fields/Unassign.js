import React, { PropTypes } from 'react';

export class Unassign extends React.Component {

  static propTypes = {
    onClick: PropTypes.func.isRequired
  };

  render() {
    return (
      <div className="dpw-popup-content-item-unassign-all">
        <a href="#" className="checkbox-link" onClick={this.props.onClick}>
          <span>Unassign</span>
          <span className="unassign-all-icon"><span /></span>
        </a>
      </div>
    );
  }
}
