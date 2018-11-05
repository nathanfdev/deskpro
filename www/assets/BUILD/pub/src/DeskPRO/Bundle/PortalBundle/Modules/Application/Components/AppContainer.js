import PropTypes from 'prop-types';
import React from 'react';
import Route from 'react-router/lib/Route';
import Router from 'react-router/lib/Router';
import browserHistory from 'react-router/lib/browserHistory';
import * as Guides from '../../Guides/Components';

class AppContainer extends React.Component {

  static propTypes = {
    routePath: PropTypes.string
  };

  render() {
    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }

    return (
      <Router history={browserHistory}>
        <Route path="/" component={Guides.ViewTopic}>
          <Route path={`${baseUrl}/guides/**/:slug`} component={Guides.ViewTopic} />
        </Route>
      </Router>
    );
  }
}
export default AppContainer;
