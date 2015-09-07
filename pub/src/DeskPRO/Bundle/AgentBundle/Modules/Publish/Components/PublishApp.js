import React from 'react';
import AppContainer from 'DeskPRO/Component/AppContainer';
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';
import { connect } from 'redux/react';

@connect(state => ({
  dp_window: state.dp_window
}))
export class PublishApp extends React.Component {

  render() {
    return (
      <AppContainer thisAppId="publish" {...this.props}>
        <NavContainer />
        <ListContainer />
      </AppContainer>
    );
  }
}
