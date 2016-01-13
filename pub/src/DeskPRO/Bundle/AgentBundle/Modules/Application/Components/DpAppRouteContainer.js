import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { showWelcomePage, doneInitialLoad } from '../../Application/Actions/appActions';
import { DpApp } from './DpApp';
import { DpAppLoading } from './DpAppLoading';
import { WelcomeBack } from '../../Welcome/Components/WelcomeBack';
import { meSelector, meStateSelector } from '../RecordStores/Selectors/meSelectors';
import { IMContainer } from '../../IM/Components/IMContainer';
import { PreferencesContainer } from './Preferences/PreferencesContainer';
import { NotificationService } from 'DeskPRO/Bundle/AgentBundle/Services/NotificationService';
import { showWelcomePageSelector, coverShownSelector } from '../Selectors/dpWindow';

@connect(state => ({
  welcomePageShown: showWelcomePageSelector(state),
  coverShown: coverShownSelector(state),
  userStatus: meStateSelector.statusSel(state),
  user: meSelector(state),
  actionAlerts: state.Application.notifications.get('actionAlerts'),
  actionAlertsSetup: state.Application.notifications.get('actionAlertsSetup')
}))
export class DpAppRouteContainer extends React.Component {

  static propTypes = {
    children: PropTypes.node.isRequired,
    welcomePageShown: PropTypes.bool.isRequired,
    coverShown: PropTypes.bool.isRequired,
    userStatus: PropTypes.object.isRequired,
    user: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    actionAlerts: PropTypes.object.isRequired,
    actionAlertsSetup: PropTypes.bool.isRequired
  };

  componentDidMount() {
    this.props.dispatch(showWelcomePage());
    this.hideWelcomePage();
  }

  componentDidUpdate() {
    this.hideWelcomePage();
  }

  componentWillUnmount() {
    clearTimeout(this.welcomePageTimer);
    if (this.ns) {
      this.ns.stopPolling();
    }
  }

  setupPolling() {
    const { user, dispatch, actionAlerts} = this.props;

    this.ns = new NotificationService({ user, dispatch, clients: actionAlerts.clients });
    this.ns.startPolling();
  }

  hideWelcomePage() {
    const { userStatus, dispatch, actionAlertsSetup } = this.props;
    // we should wait for actionAlertsSetup finished, untill hide welcomePage, otherwise polling will not start!
    if (!this.welcomePageTimer && userStatus.get('isDone') && !actionAlertsSetup) {
      this.welcomePageTimer = setTimeout(() => dispatch(doneInitialLoad()), 3000);
      this.setupPolling();
    }
  }

  render() {
    const { userStatus, user, welcomePageShown, coverShown, children } = this.props;

    if (userStatus.get('isLoading') || userStatus.get('isError')) {
      return <DpAppLoading />;
    }
    if (welcomePageShown) {
      return <WelcomeBack user={user} />;
    }

    return (
      <div>
        <DpApp>
          {children}
        </DpApp>

        {coverShown && <div className="cover"></div>}

        <IMContainer/>
        <PreferencesContainer positionTarget={document.body}/>
      </div>
    );
  }
}
