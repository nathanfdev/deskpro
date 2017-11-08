import PropTypes from 'prop-types';
import React from 'react';
import { Router, Route, hashHistory, RouterContext, IndexRoute } from 'react-router';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import DpAppRouteContainer from './DpAppRouteContainer';
import { LoginContainer } from '../../Login/Components/Login';
import { ForgottenPasswordContainer } from '../../Login/Components/ForgottenPassword';
import ExtendTrialContainer from '../../Login/Components/ExtendTrialContainer';
import { DeleteAccountFeedbackContainer } from '../../Login/Components/DeleteAccountFeedback';
import { ConfirmResetContainer } from '../../Login/Components/ConfirmReset';
import { ConfirmExtendContainer } from '../../Login/Components/ConfirmExtend';

@connect(state => ({
  me: meSelector(state)
}))
class DpAppContainer extends React.Component {
  static propTypes = {
    me: PropTypes.object
  };

  requireAuth = (nextState, replace, callback) => {
    if (!this.props.me || !this.props.me.get('can_billing')) {
      // Prevent redirect loop
      setTimeout(() => {
        replace({
          pathname: '/login',
          state:    { nextPathname: nextState.location.pathname }
        });
        callback();
      }, 100);
    } else {
      callback();
    }
  };

  render = () =>
    <Router history={hashHistory} render={props => <RouterContext {...props} />}>
      <Route path="/" component={DpAppRouteContainer}>
        <IndexRoute component={LoginContainer} />
        <Route
          name="login"
          path="/login"
          component={LoginContainer}
        />
        <Route
          name="forgot_password"
          path="/forgot-password"
          component={ForgottenPasswordContainer}
        />
        <Route
          name="extend-trial"
          path="/extend-trial"
          component={ExtendTrialContainer}
          onEnter={this.requireAuth}
        />
        <Route
          name="delete_account"
          path="/delete-account"
          component={DeleteAccountFeedbackContainer}
          onEnter={this.requireAuth}
        />
        <Route
          name="confirm_reset"
          path="/confirm-reset"
          component={ConfirmResetContainer}
          onEnter={this.requireAuth}
        />
        <Route
          name="confirm_extend"
          path="/confirm-extend"
          component={ConfirmExtendContainer}
          onEnter={this.requireAuth}
        />
      </Route>
    </Router>
}
export default DpAppContainer;
