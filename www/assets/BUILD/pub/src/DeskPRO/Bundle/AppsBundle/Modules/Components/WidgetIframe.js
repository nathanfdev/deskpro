import PropTypes from 'prop-types';
import React from 'react';

class WidgetIframe extends React.PureComponent {
  static propTypes = {
    url: PropTypes.string.isRequired,
    id:  PropTypes.string.isRequired
  };

  /**
   * @param {WidgetConfiguration} config
   * @return {*}
   */
  static fromConfiguration(config)  {
    return (<WidgetIframe id={config.canonicId} url={config.getUrl()} />);
  }

  render()  {
    const { url, id } = this.props;

    return (
      <div id={id}>
        <iframe
          scrolling={'no'}
          frameBorder={'0'}
          src={url}
          style={{ width: '100%', height: '100%', backgroundColor: 'transparent' }}
        />
      </div>
    );
  }
}

export { WidgetIframe };
