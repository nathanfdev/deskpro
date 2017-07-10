import React from 'react';
import { connect } from 'react-redux';
import OAuthClientForm from './OAuthClientForm';
import BaseOAuthClientFormContainer from './BaseOAuthClientFormContainer';
import { createOAuthClient } from '../../../Actions/oauthClientActions';

@connect()
class NewOAuthClientFormContainer extends BaseOAuthClientFormContainer {

  submitData = data => this.props.dispatch(createOAuthClient(data));

  render() {
    return (
      <OAuthClientForm
        onReturnBack={this.onReturnBack}
        onSubmit={this.onSubmit}
        onCancel={this.onReturnBack}
      />
    );
  }
}

export default NewOAuthClientFormContainer;
