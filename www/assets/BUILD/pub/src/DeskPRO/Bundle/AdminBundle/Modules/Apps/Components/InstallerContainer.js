import React, { PropTypes } from 'react';

class InstallerContainer extends React.Component {
  static propTypes = {
    match: PropTypes.object
  };


  render()  {
    return (
      <div className={'appHeader'}>
        <img className={'icon'} src="icon.png" alt="icon" />
        <h1>My App</h1>
        <p>App description</p>
      </div>
    );
  }
}

export { InstallerContainer };
