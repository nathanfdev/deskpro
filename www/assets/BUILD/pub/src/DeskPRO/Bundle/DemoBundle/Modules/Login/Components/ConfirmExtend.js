import React, { PropTypes } from 'react';
import { FormattedMessage } from 'react-intl';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';

export class ConfirmExtendContainer extends React.Component {
  static contextTypes = {
    router: PropTypes.object.isRequired
  };

  onDeleteData = () => {
    this.context.router.push('/confirm-reset');
  }

  onPreserveData = () => {

  }

  render() {
    return (
      <ConfirmExtend
        onDeleteData={this.onDeleteData}
        onPreserveData={this.onPreserveData}
      />
    );
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
        <h3>
          <FormattedMessage
            id="cloud.demo_expired.confirm_extend_title"
            defaultMessage="Great, your trial has been extended!"
          />
        </h3>
        <p>
          <FormattedMessage
            id="cloud.demo_expired.confirm_extend_desc"
            defaultMessage="Would you like to reset your trial and delete your previous data?
          Or, preserve your data from early in the trial and continue where your left off?"
          />
        </p>
        <div onClick={this.props.onDeleteData}>
          <FormattedMessage
            id="cloud.demo_expired.confirm_extend_delete"
            defaultMessage="Delete data & reset trial"
          />
        </div>
        <div className="ui horizontal divider">
          <FormattedMessage
            id="cloud.demo_expired.or"
            defaultMessage="Or"
          />
        </div>
        <div onClick={this.props.onPreserveData}>
          <FormattedMessage
            id="cloud.demo_expired.confirm_extend_preserve"
            defaultMessage="Preserve data and continue"
          />
        </div>
      </Segment>
    );
  }
}
