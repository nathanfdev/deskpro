import React, { PropTypes } from 'react';
import { InstallerIframe } from './InstallerIframe';

const dashboardStyle = {
  marginTop: '15px'
};

const InstallerErrorScreen = ({ error }) => ( // eslint-disable-line class-methods-use-this, no-unused-vars
  <div style={dashboardStyle}>
    <p>The app installer encountered an error</p>
  </div>
  );

InstallerErrorScreen.propTypes = {
  error: PropTypes.string
};


const InstallerNormalScreen = ({ installUrl }) => (
  <div style={dashboardStyle}>
    <div className={'appHeader'}>
      <img className={'icon'} src="icon.png" alt="icon" />
      <h1>My App</h1>
      <p>App description</p>
    </div>
    <InstallerIframe url={installUrl} id={'app-iframe'} />
  </div>
);

InstallerNormalScreen.propTypes = {
  installUrl: PropTypes.string
};

const InstallerLoadingScreen = () => (
  <div style={dashboardStyle}>
    <p>Loading</p>
  </div>
);


export { InstallerLoadingScreen };
export { InstallerNormalScreen };
export { InstallerErrorScreen };
