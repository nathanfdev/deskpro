import PropTypes from 'prop-types';
import React from 'react';
import { DpLogo } from '../../Login/Components/DpLogo';
import { Tip } from './Tip';

export class WelcomeBack extends React.Component {
  render() {
    // if we need to greet by name, like "Welcome back, Bob!", then the name shouldn't be stored in redux
    // as it'll be fetched only whe bootstrap is done, can store it in a cookie or local storage
    return (
      <div className="deskpro-loading">
        <DpLogo>
          <div className="deskpro-loading-blurb">
            <h1>Welcome back!</h1>
            <p>Give us a second, we're busy loading your helpdesk.</p>
          </div>

          <div className="deskpro-loading-loader">
            <span className="loader"></span>
            <div id="loader"></div>
          </div>
        </DpLogo>

        <Tip />
      </div>
    );
  }
}
