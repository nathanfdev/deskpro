import React from 'react';
import { replaceRoute } from '../../../../../Services/history';

class BaseOAuthClientFormContainer extends React.Component {

  onSubmit = (data) => {
    const promise = this.submitData(data);
    promise.success(() => this.onReturnBack());

    return promise;
  };

  onReturnBack = () => {
    replaceRoute('/apps/oauth_clients');
  };
}

export default BaseOAuthClientFormContainer;
