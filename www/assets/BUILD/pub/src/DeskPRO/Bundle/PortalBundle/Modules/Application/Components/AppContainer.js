import React, { PropTypes } from 'react';
import { Router, Route, browserHistory } from 'react-router';
import * as Guides from '../../Guides/Components';

class AppContainer extends React.Component {

  static propTypes = {
    routePath: PropTypes.string
  };

  render() {
    return (
      <Router history={browserHistory}>
        <Route path="/" component={Guides.ViewTopic}>
          <Route path="/:locale/guides/:guideSlug/:slug" component={Guides.ViewTopic} />
        </Route>
      </Router>
    );
  }
}
export default AppContainer;
