import React, { PropTypes } from 'react';
import { Router, Route } from 'react-router';
import * as Guides from '../../Guides/Components';
import { history } from '../../../Services/history';

class AppContainer extends React.Component {

  static propTypes = {
    routePath: PropTypes.string
  };

  render() {
    return (
      <Router history={history}>
        <Route path="guides/" component={Guides.ViewTopic} />
      </Router>
    );
  }
}
export default AppContainer;
