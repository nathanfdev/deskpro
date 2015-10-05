import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Header } from './Header';
import { AppSwitcher } from './AppSwitcher';
import { TabFrame } from './TabFrame';
import { routingStarted } from '../Actions/AppActions';

@connect(state => ({
  ...state,
  user: state.Application.user,
  dpWindow: state.Application.dpWindow
}))
export class DpApp extends React.Component {
  static propTypes = {
    user: PropTypes.object.isRequired,
    children: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    const { dispatch, router } = this.props;
    dispatch(routingStarted(router));
  }

  render() {
    const { user, dpWindow, dispatch, children } = this.props;

    return (
      <div className="dp-window">
        <Header user={user} dpWindow={dpWindow} />
        <AppSwitcher dpWindow={dpWindow} dispatch={dispatch} />

        {children}

        <TabFrame dpWindow={dpWindow} />
      </div>
    );
  }
}
