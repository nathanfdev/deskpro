import React from 'react';
import { connect } from 'react-redux';
import { AppContainer } from 'DeskPRO/Component/AppContainer';
import { NavContainer } from './Nav/NavContainer';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
export class ChatApp extends React.Component {
  render() {
    return (
      <AppContainer thisAppId="chat" {...this.props}>
        <NavContainer />
      </AppContainer>
    );
  }
}
