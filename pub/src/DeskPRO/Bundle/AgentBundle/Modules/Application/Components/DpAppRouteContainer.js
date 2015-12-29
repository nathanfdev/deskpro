import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import * as AppActions from '../../Application/Actions/appActions';
import { DpApp } from './DpApp';
import { DpAppLoading } from './DpAppLoading';
import { WelcomeBack } from '../../Welcome/Components/WelcomeBack';
import { meSelector, meStateSelector } from '../RecordStores/Selectors/meSelectors';
import { IMContainer } from '../../IM/Components/IMContainer';
import { PreferencesContainer } from './Preferences/PreferencesContainer';
import { newActionAlerts } from '../Actions/notificationActions';
import PusherClient from 'DeskPRO/Component/Notification/Client/PusherClient';
import EventEmitter2 from 'eventemitter2';

@connect(state => ({
  dpWindow: state.Application.dpWindow,
  userStatus: meStateSelector.statusSel(state),
  user: meSelector(state)
}))
export class DpAppRouteContainer extends React.Component {

  static propTypes = {
    children: PropTypes.node.isRequired,
    dpWindow: PropTypes.object.isRequired,
    userStatus: PropTypes.object.isRequired,
    user: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  componentDidMount() {
    this.props.dispatch(AppActions.showWelcomePage());
    this.hideWelcomePage();
  }

  componentDidUpdate() {
    this.hideWelcomePage();
  }

  componentWillUnmount() {
    clearTimeout(this.welcomePageTimer);
    // clearInterval(this.pollingInterval);
  }

  setupPolling() {
    const { user, dispatch } = this.props;
    const eventEmitter = new EventEmitter2({
      wildcard: false,
      delimiter: '::',
      newListener: false,
      maxListeners: 10
    });
    const pusher = new PusherClient(
      {
        appKey: 'eaa00fb39fddc251d116',
        me: user.get('id'),
        dispatcher: eventEmitter.emit.bind(eventEmitter),
        debug: true
      }
    );
    eventEmitter.on('action_alert', (data) => dispatch(newActionAlerts(data)));
    pusher.bind('private-channel-' + user.get('id'), 'action_alert');
  }

  hideWelcomePage() {
    const { userStatus, dispatch } = this.props;
    if (!this.welcomePageTimer && userStatus.get('isDone')) {
      this.welcomePageTimer = setTimeout(() => dispatch(AppActions.doneInitialLoad()), 3000);
      // this.pollingInterval = setInterval(() => this.props.dispatch(pollActionAlerts()), 25000);
      this.setupPolling();
    }
  }

  render() {
    const { userStatus, user, dpWindow, children } = this.props;

    if (userStatus.get('isLoading') || userStatus.get('isError')) {
      return <DpAppLoading />;
    }
    if (dpWindow.get('showWelcomePage')) {
      return <WelcomeBack user={user} />;
    }

    return (
      <div>
        <DpApp>
          {children}
        </DpApp>

        {dpWindow.get('coverShown') && <div className="cover"></div>}

        <IMContainer/>
        <PreferencesContainer positionTarget={document.body}/>
      </div>
    );
  }
}
