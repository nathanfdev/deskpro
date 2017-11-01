import PropTypes from 'prop-types';
import React from 'react';
import { pureRender } from 'Ampliflux';

@pureRender
export class MarkDoneButton extends React.Component {

  static propTypes = {
    isDone:   PropTypes.bool,
    onToggle: PropTypes.func
  };

  onToggle = () => {
    if (this.props.onToggle) {
      this.props.onToggle(!this.props.isDone);
    }
  };

  render() {
    const { isDone } = this.props;

    if (isDone) {
      return (
        <div className="dpw--single-card-mark-done dpw--single-card-mark-done-minimized" onClick={this.onToggle}>
          <span className="done">Done</span>
        </div>
      );
    }

    return (
      <div className="dpw--single-card-mark-done" onClick={this.onToggle}>

        <i className="fa fa-check" />
        <span>Mark Done</span>
      </div>
    );
  }
}
