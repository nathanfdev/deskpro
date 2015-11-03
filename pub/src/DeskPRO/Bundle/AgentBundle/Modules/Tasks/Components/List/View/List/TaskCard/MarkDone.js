import React, { PropTypes } from 'react';

export class MarkDone extends React.Component {

  static propTypes = {
    isDone: PropTypes.bool,
    onToggle: PropTypes.func.isRequired
  };

  render() {
    const { isDone, onToggle } = this.props;

    if (isDone) {
      return (
        <div className="dpw--single-card-mark-done dpw--single-card-mark-done-minimized"
             onClick={onToggle}>

          <span>Done</span>
          <i className="fa fa-check"/>
        </div>
      );
    }

    return (
      <div className="dpw--single-card-mark-done"
           onClick={onToggle}>

        <i className="fa fa-check"/>
        <span>Mark Done</span>
      </div>
    );
  }
}
