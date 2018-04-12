import PropTypes from 'prop-types';
import React from 'react';
import Error  from './Error';

const ScreenInstallerError = ({ error }) =>  // eslint-disable-line class-methods-use-this, no-unused-vars
   (
     <div className="panel-body">
       <h2>The app installer encountered an error.</h2>

       <p>If you frequently experience this error, copy the error contents from below and send a bug report to <a href="mailto:support@deskpro.com" target="_top">support@deskpro.com</a></p>

       <Error error={error} />
     </div>
  );

ScreenInstallerError.propTypes = {
  error: PropTypes.object.isRequired
};

export { ScreenInstallerError };
