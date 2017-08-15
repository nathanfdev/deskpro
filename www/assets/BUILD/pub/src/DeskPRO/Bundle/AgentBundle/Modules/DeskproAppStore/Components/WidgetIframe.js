import React from 'react';

export const WidgetIframe = ({ url, id }) => (
  <div id={id}>
    <iframe
      scrolling={'no'}
      frameBorder={'0'}
      src={url}
      style={{ width: '100%', height: '100%', backgroundColor: 'transparent' }}
    />
  </div>
  );

WidgetIframe.propTypes = {
  url: React.PropTypes.string.isRequired,
  id:  React.PropTypes.string.isRequired
};
