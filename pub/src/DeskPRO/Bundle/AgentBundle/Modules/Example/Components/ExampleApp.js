import React from 'react';
import { connect } from 'react-redux';
import { AppContainer } from 'DeskPRO/Component/AppContainer';
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';

export class ExampleApp extends React.Component {
  render() {
    return (
      <AppContainer thisAppId="example">
        <NavContainer/>
        <ListContainer/>
      </AppContainer>
    );
  }
}
