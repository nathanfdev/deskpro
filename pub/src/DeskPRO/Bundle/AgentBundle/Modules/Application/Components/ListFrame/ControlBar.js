import React from 'react';
import { connect } from 'redux/react';

export class ControlBar extends React.Component {
  render() {
    return (
      <div className="control-bar">
        {this.props.children}
      </div>
    );
  }
}


