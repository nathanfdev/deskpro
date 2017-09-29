import React, { PropTypes } from 'react';


const ScreenInstallerError = ({ error }) => ( // eslint-disable-line class-methods-use-this, no-unused-vars
  <p>The app installer encountered an error</p>
);

ScreenInstallerError.propTypes = {
  error: PropTypes.string
};

export { ScreenInstallerError };
