import React from 'react';
import { connect } from 'react-redux';
import { Header } from './Header';
import { AppSwitcher } from './AppSwitcher';
import TabFrame from './TabFrame';
import { routingStarted } from '../Actions/AppActions';

@connect(state => ({
  ...state,
  user: state.Application.user,
  dpWindow: state.Application.dpWindow
}))
export default class DpApp extends React.Component {
  constructor(props) {
    super(props);
    const { dispatch, router } = this.props;
    dispatch(routingStarted(router));
  }

  render() {
    const { user, dpWindow, dispatch } = this.props;

    return (
      <div className="dp-window">
        <Header user={user} />
        <AppSwitcher dpWindow={dpWindow} dispatch={dispatch} />

        {this.props.children}

        <TabFrame dpWindow={dpWindow} />
      </div>
    );
  }
}
