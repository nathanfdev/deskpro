import React from 'react';
import AppContainer from 'DeskPRO/Component/AppContainer';
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';
import { connect } from 'redux/react';

@connect(state => ({
  dp_window: state.dp_window
}))
export class ChatApp extends React.Component {

  render() {
    return (
      <AppContainer thisAppId="chat" {...this.props}>
        <NavContainer dp_window={this.props.dp_window} />
        <ListContainer />
      </AppContainer>
    );
  }
}
