import React, { PropTypes } from 'react';

export class MarkDoneButton extends React.Component {

  static propTypes = {
    isDone: PropTypes.bool,
    onToggle: PropTypes.func
  };

  shouldComponentUpdate(props) {
    return this.props.isDone !== props.isDone;
  }

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
