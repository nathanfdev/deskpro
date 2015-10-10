import React from 'react';
import { connect } from 'react-redux';
import { AppContainer } from 'DeskPRO/Component/AppContainer';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
export class LoginApp extends React.Component {
  render() {
    return (
      <AppContainer thisAppId="login" {...this.props}>
        <div></div>
      </AppContainer>
    );
  }
}
