import React from 'react';
import { replaceRoute } from '../../../../../Services/history';

class BaseQueueFormContainer extends React.Component {

  onSubmit = (data) => {
    const promise = this.submitData(data);
    promise.success(() => {
      replaceRoute('/voice_channel/queues');
    });

    return promise;
  };

  onReturnBack = () => {
    replaceRoute('/voice_channel/queues');
  };
}

export default BaseQueueFormContainer;
