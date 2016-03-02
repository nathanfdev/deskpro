import React, { PropTypes } from 'react';

export class MarkDoneButton extends React.Component {

  static propTypes = {
    isDone: PropTypes.bool,
    onToggle: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      isDone: props.isDone
    }
  }

  componentWillReceiveProps(props) {
    this.setState({isDone: props.isDone});
  }

  shouldComponentUpdate(props, state) {
    return this.state.isDone !== state.isDone;
  }

  render() {
    const { onToggle } = this.props;
    const { isDone } = this.state;

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
