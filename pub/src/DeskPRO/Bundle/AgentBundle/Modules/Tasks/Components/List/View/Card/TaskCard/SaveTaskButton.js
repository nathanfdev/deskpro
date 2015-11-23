import React, { PropTypes } from 'react';

export class SaveTaskButton extends React.Component {

  static propTypes = {
    onClick: PropTypes.func
  };

  render() {
    return (
      <div className="dpw--single-card-mark-done hovered"
           onClick={this.props.onClick}>

        <i className="fa fa-save"/>
        <span>Save Task</span>
      </div>
    );
  }
}
