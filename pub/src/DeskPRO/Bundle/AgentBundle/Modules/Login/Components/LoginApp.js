import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { DpLogo } from './DpLogo';
import { LeftPanel } from './Form/LeftPanel';
import { RightPanel } from './Form/RightPanel';
import { LoginFormFooter } from './Form/LoginFormFooter';
import { hasAuthSelector } from '../Selectors/login';

@connect(state => ({
  hasAuth: hasAuthSelector(state)
}))
export class LoginApp extends React.Component {

  static propTypes = {
    history: PropTypes.object.isRequired,
    hasAuth: PropTypes.bool
  };

  componentDidMount() {
    const { hasAuth, history } = this.props;

    if (hasAuth) {
      history.replace(`${DP_BASE_URL_RELATIVE}/agent/`);
    }
  }

  render() {
    return (
      <div className="deskpro-loading">
        <DpLogo />

        <div className="dpw-login-panels">
          <LeftPanel />
          <RightPanel />
        </div>

        <LoginFormFooter />
      </div>
    );
  }
}
