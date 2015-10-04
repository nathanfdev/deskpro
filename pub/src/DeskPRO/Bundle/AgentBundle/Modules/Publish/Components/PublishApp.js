import React from 'react';
import AppContainer from 'DeskPRO/Component/AppContainer';
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';
import { connect } from 'react-redux';

@connect(state => ({
  dp_window: state.Application.dp_window
}))
export class PublishApp extends React.Component {

  render() {
    return (
      <AppContainer thisAppId="publish" {...this.props}>
        <NavContainer dp_window={this.props.dp_window} />
        <ListContainer />
      </AppContainer>
    );
  }
}
