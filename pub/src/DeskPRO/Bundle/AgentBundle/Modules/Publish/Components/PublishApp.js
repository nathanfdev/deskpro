import React from 'react';
import AppContainer from 'DeskPRO/Component/AppContainer';
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';
import { connect } from 'react-redux';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
export class PublishApp extends React.Component {

  render() {
    return (
      <AppContainer thisAppId="publish" {...this.props}>
        <NavContainer dpWindow={this.props.dpWindow} />
        <ListContainer />
      </AppContainer>
    );
  }
}
