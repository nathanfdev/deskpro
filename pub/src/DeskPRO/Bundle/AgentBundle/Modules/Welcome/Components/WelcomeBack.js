import React, { PropTypes } from 'react';
import { DpLogo } from '../../Login/Components/DpLogo';
import { Tip } from './Tip';

export class WelcomeBack extends React.Component {

  static propTypes = {
    user: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired
  };

  componentDidMount() {
    const history = this.props.history;
    setTimeout(() => history.pushState(null, `${DP_BASE_URL_RELATIVE}/agent/tasks`), 3000);
  }

  render() {
    return (
      <div className="deskpro-loading">
        <DpLogo>
          <div className="deskpro-loading-blurb">
            <h1>Welcome back, {this.props.user.get('first_name')}</h1>
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
