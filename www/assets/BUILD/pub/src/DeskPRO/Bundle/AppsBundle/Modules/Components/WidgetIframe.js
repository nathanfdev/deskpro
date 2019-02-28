import PropTypes from 'prop-types';
import React from 'react';

class WidgetIframe extends React.PureComponent {

  static propTypes = {
    isFullscreen:  PropTypes.bool.isRequired,
    url:           PropTypes.string.isRequired,
    id:            PropTypes.string.isRequired,
    onWindowReady: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.iframeRef = React.createRef();
  }

  onWindowReady(iframe) {
    if (iframe) {
      this.props.onWindowReady(iframe.contentWindow);
    } else {
      this.props.onWindowReady(null);
    }
  };

  componentDidMount() {
    this.onWindowReady(this.iframeRef.current);
  }

  render()  {
    return (
      <div id={this.props.id} className={this.props.isFullscreen ? 'apps-window apps-window--is-fullscreen dp-AppPanel' : 'apps-window'}>
        <iframe
          ref={this.iframeRef}
          scrolling={'no'}
          frameBorder={'0'}
          src={this.props.url}
          style={{ width: '100%', height: '100%', backgroundColor: 'transparent' }}
        />
      </div>
    );
  }
}

export { WidgetIframe };
