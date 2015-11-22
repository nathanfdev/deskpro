import React, { PropTypes } from 'react';

export class SaveTaskButton extends React.Component {

  static propTypes = {
    onClick: PropTypes.func
  };

  render() {
    const { onClick } = this.props;

    return (
      <div className="dpw--single-card-mark-done"
           onClick={onClick}>

        <i className="fa fa-save"/>
        <span>Save Task</span>
      </div>
    );
  }
}
