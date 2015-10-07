import React from 'react';
import { connect } from 'react-redux';
import { AppContainer } from 'DeskPRO/Component/AppContainer';
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
export class CrmApp extends React.Component {
  render() {
    return (
      <AppContainer thisAppId="crm" {...this.props}>
        <NavContainer />
      </AppContainer>
    );
  }
}
