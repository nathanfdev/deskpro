import React from 'react';
import { replaceRoute } from '../../../../../Services/history';

class BaseQueueFormContainer extends React.Component {

  onSubmit = (data) => {
    this.setState({
      saving: true,
      errors: {}
    });

    const promise = this.submitData(data);
    promise.success(() => {
      replaceRoute('/voice_channel/queues');
    });
    promise.error((result) => {
      this.setState({
        errors: result.errors,
        saving: false
      });
    });
  };

  onReturnBack = () => {
    replaceRoute('/voice_channel/queues');
  };
}

export default BaseQueueFormContainer;
