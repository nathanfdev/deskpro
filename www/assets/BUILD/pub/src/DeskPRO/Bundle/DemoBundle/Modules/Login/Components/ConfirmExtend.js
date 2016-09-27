import React, { PropTypes } from 'react';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';

export class ConfirmExtendContainer extends React.Component {
  render() {
    return <ConfirmExtend />;
  }
}
export class ConfirmExtend extends React.Component {
  static propTypes = {
    onDeleteData:   PropTypes.func,
    onPreserveData: PropTypes.func
  };

  render() {
    return (
      <Segment className="confirm-extend">
        <h3>Great, your trial has been extended!</h3>
        <p>
          Would you like to reset your trial and delete your previous data?
          Or, preserve your data from early in the trial and continue where your left off?
        </p>
        <div onClick={this.props.onDeleteData}>
          Delete data & reset trial
        </div>
        <div className="ui horizontal divider">
          Or
        </div>
        <div onClick={this.props.onPreserveData}>
          Preserve data and continue
        </div>
      </Segment>
    );
  }
}
