import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import * as AppActions from '../../Application/Actions/appActions';
import { DpApp } from './DpApp';
import { DpAppLoading } from './DpAppLoading';
import { WelcomeBack } from '../../Welcome/Components/WelcomeBack';
import { meSelector, meStateSelector } from '../RecordStores/Selectors/meSelectors';
import { IMContainer } from '../../IM/Components/IMContainer';
import { PreferencesContainer } from './Preferences/PreferencesContainer';

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
  }

  hideWelcomePage() {
    const { userStatus, dispatch } = this.props;

    if (!this.welcomePageTimer && userStatus.get('isDone')) {
      this.welcomePageTimer = setTimeout(() => dispatch(AppActions.doneInitialLoad()), 3000);
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
