import React, { PropTypes } from 'react';
import ExtendTrial from './ExtendTrial';

class ExtendTrialContainer extends React.Component {
  static contextTypes = {
    router: PropTypes.object.isRequired
  };

  onDeleteAccount = () => {
    this.context.router.push('/delete-account');
  };

  onResumeTrial = () => {
    // Do API call and then
    this.context.router.push('/confirm-extend');
  };

  render() {
    return (
      <ExtendTrial
        onDeleteAccount={this.onDeleteAccount}
        onResumeTrial={this.onResumeTrial}
      />
    );
  }
}
export default ExtendTrialContainer;
