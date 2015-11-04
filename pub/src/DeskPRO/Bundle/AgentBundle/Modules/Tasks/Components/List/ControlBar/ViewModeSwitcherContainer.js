import React from 'react';
import { connect } from 'react-redux';
import { ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

@connect()
export class ViewModeSwitcherContainer extends React.Component {

  render() {
    return (
      <ViewModeSwitcher {...this.props} />
    );
  }
}
