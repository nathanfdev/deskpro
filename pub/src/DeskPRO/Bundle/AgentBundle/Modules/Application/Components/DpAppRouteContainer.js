import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import * as AppActions from '../../Application/Actions/AppActions';
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
    this.welcomePageTimer = false;
    this.props.dispatch(AppActions.showWelcomePage());
  }

  componentWillUnmount() {
    clearTimeout(this.welcomePageTimer);
    this.props.dispatch(AppActions.showWelcomePage());
  }

  render() {
    const { userStatus, user, dpWindow, children, dispatch } = this.props;
    if (userStatus.get('isLoading') || userStatus.get('isError')) {
      return <DpAppLoading />;
    }

    if (dpWindow.get('showWelcomePage')) {
      this.welcomePageTimer = setTimeout(() => dispatch(AppActions.hideWelcomePage()), 3000);
      return (
        <WelcomeBack user={user} />
      );
    }

    return (
      <div>
        <DpApp>
          {children}
        </DpApp>

        {dpWindow.get('coverShown') ? (<div className="cover"></div>) : null}

        <IMContainer/>
        <PreferencesContainer positionTarget={document.body}/>
      </div>
    );
  }
}
