import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import OAuthClientForm from './OAuthClientForm';
import BaseOAuthClientFormContainer from './BaseOAuthClientFormContainer';
import { loadOAuthClients, updateOAuthClient, deleteOAuthClient } from '../../../Actions/oauthClientActions';
import { allOAuthClientsSelector, isOAuthClientsLoadedSelector } from '../../../Selectors/oauthClients';

@connect(state => ({
  oauthClients:       allOAuthClientsSelector(state),
  oauthClientsLoaded: isOAuthClientsLoadedSelector(state)
}))
class EditOAuthClientFormContainer extends BaseOAuthClientFormContainer {

  static propTypes = {
    dispatch:           PropTypes.func,
    params:             PropTypes.object,
    oauthClients:       PropTypes.object,
    oauthClientsLoaded: PropTypes.bool
  };

  componentDidMount() {
    this.props.dispatch(loadOAuthClients());
  }

  onDelete = () => {
    const { dispatch } = this.props;
    const promise = dispatch(deleteOAuthClient(this.getClientId()));
    promise.success(() => this.onReturnBack());

    return promise;
  };

  submitData = data => this.props.dispatch(updateOAuthClient(this.getClientId(), data));
  getClientId = () => parseInt(this.props.params.clientId, 10);

  render() {
    const { oauthClients, oauthClientsLoaded } = this.props;
    if (!oauthClientsLoaded) {
      return <LoadingPage />;
    }

    return (
      <OAuthClientForm
        client={oauthClients.get(this.getClientId())}
        onReturnBack={this.onReturnBack}
        onSubmit={this.onSubmit}
        onDelete={this.onDelete}
        onCancel={this.onReturnBack}
      />
    );
  }
}

export default EditOAuthClientFormContainer;
