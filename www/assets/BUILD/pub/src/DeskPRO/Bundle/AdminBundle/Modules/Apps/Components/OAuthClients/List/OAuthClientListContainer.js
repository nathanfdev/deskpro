import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import OAuthClientList from './OAuthClientList';
import { loadOAuthClients } from '../../../Actions/oauthClientActions';
import { allOAuthClientsSelector, isOAuthClientsLoadedSelector } from '../../../Selectors/oauthClients';
import { replaceRoute } from '../../../../../Services/history';

@connect(state => ({
  oauthClients:       allOAuthClientsSelector(state),
  oauthClientsLoaded: isOAuthClientsLoadedSelector(state)
}))
class OAuthClientListContainer extends React.Component {

  static propTypes = {
    dispatch:           PropTypes.func,
    oauthClientsLoaded: PropTypes.bool
  };

  componentDidMount() {
    this.props.dispatch(loadOAuthClients());
  }

  addClient = () => {
    replaceRoute('/apps/oauth_clients/new');
  };

  editClient = (client) => {
    replaceRoute(`/apps/oauth_clients/${client.get('id')}`);
  };

  render() {
    const { oauthClientsLoaded } = this.props;

    if (!oauthClientsLoaded) {
      return <LoadingPage />;
    }

    return (
      <OAuthClientList
        {...this.props}
        addClient={this.addClient}
        editClient={this.editClient}
      />
    );
  }
}

export default OAuthClientListContainer;
