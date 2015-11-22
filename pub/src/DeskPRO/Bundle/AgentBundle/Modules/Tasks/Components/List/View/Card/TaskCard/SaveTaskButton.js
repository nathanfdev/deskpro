import React, { PropTypes } from 'react';

export class SaveTaskButton extends React.Component {

  static propTypes = {
    isValid: PropTypes.bool,
    onClick: PropTypes.func
  };

  onClick = () => {
    const { isValid, onClick } = this.props;
    if (isValid) {
      onClick();
    }
  };

  render() {
    return (
      <div className="dpw--single-card-mark-done hovered"
           onClick={this.onClick}>

        <i className="fa fa-save"/>
        <span>Save Task</span>
      </div>
    );
  }
}
