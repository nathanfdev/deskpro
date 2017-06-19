import React from 'react';
import { replaceRoute } from '../../../../../Services/history';

class BaseAutoAttendantFormContainer extends React.Component {

  onSubmit = (data) => {
    const promise = this.submitData(data);
    promise.success(() => {
      replaceRoute('/voice_channel/auto_attendants');
    });

    return promise;
  };

  onReturnBack = () => {
    replaceRoute('/voice_channel/auto_attendants');
  };
}

export default BaseAutoAttendantFormContainer;
