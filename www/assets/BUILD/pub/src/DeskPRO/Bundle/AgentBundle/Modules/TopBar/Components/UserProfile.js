import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import StatusFormContainer from './StatusForm/StatusFormContainer';

class UserProfile extends React.Component {

  static propTypes = {
    onClickPreferences: PropTypes.func,
    onClickHelp:        PropTypes.func,
    onClickLogout:      PropTypes.func
  };

  render() {
    const { onClickPreferences, onClickHelp, onClickLogout } = this.props;

    return (
      <div className="voice-profile">
        <div className="voice-header">
          <FormattedMessage id="agent.general.your_profile" />
        </div>
        <div>
          {window.DP_HAS_VOICE && <StatusFormContainer />}
          <div className="voice-profile-menu">
            <div className="voice-profile-menu-item preferences" onClick={onClickPreferences}>
              <i className="fa fa-gear" />
              <FormattedMessage id="agent.chrome.link_preferences" />
            </div>
            <div className="voice-profile-menu-item help" onClick={onClickHelp}>
              <i className="fa fa-question-circle" />
              <FormattedMessage id="agent.chrome.link_help" />
            </div>
            <div className="voice-profile-menu-item logout" onClick={onClickLogout}>
              <i className="fa fa-reply" />
              <FormattedMessage id="agent.chrome.link_logout" />
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default UserProfile;
