import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Header } from './Header';
import { AppSwitcher } from './AppSwitcher';
import { TabFrame } from './TabFrame';
import { routingStarted } from '../Actions/routingActions';
import { NotificationsContainer } from './Notifications/notifications';
import { meSelector } from '../RecordStores/Selectors/meSelectors';

@connect(state => ({
  ...state,
  user: meSelector(state),
  dpWindow: state.Application.dpWindow
}))
export class DpApp extends React.Component {

  static propTypes = {
    user: PropTypes.object.isRequired,
    children: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  render() {
    const { user, dpWindow, dispatch, children } = this.props;

    return (
      <div className="dp-window">
        <Header user={user} />
        <AppSwitcher dpWindow={dpWindow} dispatch={dispatch} />

        {children}

        <TabFrame dpWindow={dpWindow} />
        <NotificationsContainer />
      </div>
    );
  }
}
