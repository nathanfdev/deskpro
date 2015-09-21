import React from 'react';

export default class Card extends React.Component {
  render() {
    const statusBars = this.props.statusBars ? true : false;
    const cardType = this.props.cardType ? this.props.cardType : false;

    const classes = ['dpmw--single-card'];

    if (cardType && cardType === 'task') {
      classes.push('dpmw--single-task-card');

      if (this.props.task.is_done) {
        classes.push('minimized');
      }
    }

    return (
      <div className={classes.join(' ')}>
        { cardType && cardType === 'task' ?
          (this.props.task.is_done ?
            <div className="dpw--single-card-mark-done dpw--single-card-mark-done-minimized" onClick={this.props.doneAction.bind(this)}>
              <span>Done</span>
              <i className="fa fa-check" />
            </div>
            : <div className="dpw--single-card-mark-done" onClick={this.props.doneAction.bind(this)}>
                <i className="fa fa-check" />
                <span>Mark Done</span>
              </div>)
        : '' }

        { statusBars ?
          <span>
            <div className="dpw--card-status-bar dpw--status-bar-left level-5" />
            <div className="dpw--card-status-bar dpw--status-bar-right level-5" />
          </span>
        : '' }

        {this.props.children}
      </div>);
  }
}
