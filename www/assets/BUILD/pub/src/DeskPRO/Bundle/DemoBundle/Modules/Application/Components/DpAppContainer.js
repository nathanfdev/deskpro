import React from 'react';
import { Router, Route, hashHistory, RouterContext, IndexRoute } from 'react-router';
import { connect } from 'react-redux';
import DpAppRouteContainer from './DpAppRouteContainer';
import { LoginContainer } from '../../Login/Components/Login';
import { ForgottenPasswordContainer } from '../../Login/Components/ForgottenPassword';
import { ExtendTrialContainer } from '../../Login/Components/ExtendTrial';
import { DeleteAccountFeedbackContainer } from '../../Login/Components/DeleteAccountFeedback';
import { ConfirmResetContainer } from '../../Login/Components/ConfirmReset';
import { ConfirmExtendContainer } from '../../Login/Components/ConfirmExtend';

@connect()
class DpAppContainer extends React.Component {
  render = () =>
    <Router history={hashHistory} render={props => <RouterContext {...props} />}>
      <Route path="/" component={DpAppRouteContainer}>
        <IndexRoute component={LoginContainer} />
        <Route name="login" path="/login" component={LoginContainer} />
        <Route name="forgot_password" path="/forgot-password" component={ForgottenPasswordContainer} />
        <Route name="extend-trial" path="/extend-trial" component={ExtendTrialContainer} />
        <Route name="delete_account" path="/delete-account" component={DeleteAccountFeedbackContainer} />
        <Route name="confirm_reset" path="/confirm-reset" component={ConfirmResetContainer} />
        <Route name="confirm_extend" path="/confirm-extend" component={ConfirmExtendContainer} />
      </Route>
    </Router>
}
export default DpAppContainer;
