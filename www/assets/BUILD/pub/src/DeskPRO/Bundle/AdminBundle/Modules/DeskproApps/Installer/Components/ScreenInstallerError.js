import React, { PropTypes } from 'react';

const dashboardStyle = {
  marginTop: '15px'
};

const ScreenInstallerError = ({ error }) => ( // eslint-disable-line class-methods-use-this, no-unused-vars
  <div style={dashboardStyle}>
    <p>The app installer encountered an error</p>
  </div>
);

ScreenInstallerError.propTypes = {
  error: PropTypes.string
};

export { ScreenInstallerError };
