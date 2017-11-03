import React, { PropTypes } from 'react';
import { Provider } from 'react-redux';
import { Router, Route } from 'react-router';
import { api, setApi, loadRepositoriesConfig } from 'DeskPRO/Bundle/AppBundle/DAL';
import { repositoriesConfig } from 'DeskPRO/Bundle/ReportBundle/DAL/config';
import store from '../../../Services/store';
import { history } from '../../../Services/history';
import Wrapper from '../../Stats/Components/Wrapper';
import { loadReports, loadGroupParams } from '../Actions/reportActions';

class AppContainer extends React.Component {

  static propTypes = {
    routePath: PropTypes.string
  };

  constructor(props) {
    super(props);

    // Bootstrap API and DAL
    setApi(api);
    loadRepositoriesConfig(repositoriesConfig);
    store.dispatch(loadReports());
    store.dispatch(loadGroupParams());
  }

  componentWillMount() {
    history.replace(this.props.routePath);
  }

  render() {
    return (
      <Provider store={store}>
        <Router history={history}>
          <Route path="stats" component={Wrapper} />
        </Router>
      </Provider>
    );
  }
}

export default AppContainer;
