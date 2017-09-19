import React, { PropTypes } from 'react';

export const InstallerIframe = ({ url, id }) => (
  <div id={id}>
    <iframe
      scrolling={'no'}
      frameBorder={'0'}
      src={url}
      style={{ width: '100%', height: '100%', backgroundColor: 'transparent' }}
    />
  </div>
);

InstallerIframe.propTypes = {
  url: PropTypes.string.isRequired,
  id:  PropTypes.string.isRequired
};
