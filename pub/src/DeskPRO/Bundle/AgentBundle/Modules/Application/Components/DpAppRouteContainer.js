import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { showWelcomePage, doneInitialLoad } from '../../Application/Actions/appActions';
import { DpApp } from './DpApp';
import { DpAppLoading } from './DpAppLoading';
import { WelcomeBack } from '../../Welcome/Components/WelcomeBack';
import { meSelector, meStateSelector } from '../RecordStores/Selectors/meSelectors';
import { IMContainer } from '../../IM/Components/IMContainer';
import { PreferencesContainer } from './Preferences/PreferencesContainer';
import { NotificationServiceContainer } from './Notifications/NotificationServiceContainer.js';
import { showWelcomePageSelector, coverShownSelector } from '../Selectors/dpWindow';

@connect(state => ({
  welcomePageShown: showWelcomePageSelector(state),
  coverShown: coverShownSelector(state),
  userStatus: meStateSelector.statusSel(state),
  user: meSelector(state)
}))
export class DpAppRouteContainer extends React.Component {

  static propTypes = {
    children: PropTypes.node.isRequired,
    welcomePageShown: PropTypes.bool.isRequired,
    coverShown: PropTypes.bool.isRequired,
    userStatus: PropTypes.object.isRequired,
    user: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
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
  }

  hideWelcomePage() {
    const { userStatus, dispatch } = this.props;
    if (!this.welcomePageTimer && userStatus.get('isDone')) {
      this.welcomePageTimer = setTimeout(() => dispatch(doneInitialLoad()), 3000);
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
        <NotificationServiceContainer/>
        <IMContainer/>
        <PreferencesContainer positionTarget={document.body}/>
      </div>
    );
  }
}
