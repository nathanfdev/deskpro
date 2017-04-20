import React from 'react';

class LoadingPage extends React.Component {

  render() {
    return (
      <div className="page">
        <div className="ui active inverted dimmer">
          <div className="ui text loader">Loading</div>
        </div>
      </div>
    );
  }
}

export default LoadingPage;
