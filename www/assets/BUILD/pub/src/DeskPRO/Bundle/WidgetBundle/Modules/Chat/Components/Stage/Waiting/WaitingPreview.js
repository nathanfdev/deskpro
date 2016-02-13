import React from 'react';

export class WaitingPreview extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-collect-user-info-waiting">
        <div>
          We are finding you an agent...
        </div>
        <div className="spinner">
          <i />
        </div>
      </div>
    );
  }
}
