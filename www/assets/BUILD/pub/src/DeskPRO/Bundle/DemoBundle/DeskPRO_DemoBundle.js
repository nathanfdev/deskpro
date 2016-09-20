import React from 'react';
import { render } from 'react-dom';
import { Router, Route, hashHistory, RouterContext, IndexRoute } from 'react-router';
import { DemoApp } from './DemoApp';
import { LoginContainer } from './Modules/Login/Components/Login';
import { ForgottenPasswordContainer } from './Modules/Login/Components/ForgottenPassword';
import { ExtendTrialContainer } from './Modules/Login/Components/ExtendTrial';
import { DeleteAccountFeedbackContainer } from './Modules/Login/Components/DeleteAccountFeedback';
import { ConfirmResetContainer } from './Modules/Login/Components/ConfirmReset';
import { ConfirmExtendContainer } from './Modules/Login/Components/ConfirmExtend';

render((
  <Router history={hashHistory} render={props => <RouterContext {...props} />} >
    <Route path="/" component={DemoApp}>
      <IndexRoute component={LoginContainer} />
      <Route path="/login" component={LoginContainer} />
      <Route path="/forgot-password" component={ForgottenPasswordContainer} />
      <Route path="/extend-trial" component={ExtendTrialContainer} />
      <Route path="/delete-account" component={DeleteAccountFeedbackContainer} />
      <Route path="/confirm-reset" component={ConfirmResetContainer} />
      <Route path="/confirm-extend" component={ConfirmExtendContainer} />
    </Route>
  </Router>
), document.getElementById('app'));
