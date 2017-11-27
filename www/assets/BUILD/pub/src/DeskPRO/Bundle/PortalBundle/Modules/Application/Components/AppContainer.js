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
    let guidePath;
    if (window.DESKPRO_ENABLED_LANGS.length > 1) {
      guidePath = '/:locale/guides/**/:slug';
    } else {
      guidePath = '/guides/**/:slug';
    }
    return (
      <Router history={browserHistory}>
        <Route path="/" component={Guides.ViewTopic}>
          <Route path={guidePath} component={Guides.ViewTopic} />
        </Route>
      </Router>
    );
  }
}
export default AppContainer;
