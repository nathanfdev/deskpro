import React from 'react';
import Loader from 'react-loader';

export class WaitingPreview extends React.Component {

  render() {
    return (

      <div>
        <div>
          We are finding you an agent...
        </div>
        <Loader color="green"
                width={3}
                left="50%"
                top="50%" />
      </div>
    );
  }
}
