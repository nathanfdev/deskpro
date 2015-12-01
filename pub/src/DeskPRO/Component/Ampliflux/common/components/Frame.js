import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import jQuery from 'jquery';

export default class Frame extends React.Component {

  static propTypes = {
    name: PropTypes.string,
    frameStyles: PropTypes.object,
    containerStyles: PropTypes.object,
    isVisible: PropTypes.bool,
    positionMode: PropTypes.string,
    children: PropTypes.node
  };

  constructor(props) {
    super(props);
    this.state = {
      dimensions: {
        width: 0,
        height: 0
      }
    };
  }

  componentDidMount() {
    this.renderFrameContents();
  }

  componentDidUpdate() {
    this.renderFrameContents();
  }

  componentWillUnmount() {
    React.unmountComponentAtNode(this.getContentDocument().body);
  }

  getDOMNode() {
    return ReactDOM.findDOMNode(this.refs.iframe);
  }

  getContentDocument() {
    return this.getDOMNode().contentDocument;
  }

  getFrameStyles() {
    const { frameStyles = {}, isVisible, positionMode } = this.props;

    let position;
    switch (positionMode) {
      case 'top.left':
        position = {left: 0, top: 0};
        break;
      case 'top.right':
        position = {right: 0, top: 0};
        break;
      case 'bottom.left':
        position = {left: 0, bottom: 0};
        break;
      case 'bottom.right':
      default:
        position = {right: 0, bottom: 0};
        break;
    }

    return {
      border: 'none',
      background: 'transparent',
      zIndex: 99999,
      width: this.state.dimensions.width,
      height: this.state.dimensions.height,
      position: 'fixed',
      display: isVisible ? 'block' : 'none',

      ...frameStyles,
      ...position
    };
  }

  autoFrameDimensions() {
    const { frameStyles = {} } = this.props;
    const doc = this.getContentDocument();

    const $container = jQuery(doc.body.firstChild);
    const width = frameStyles.width || $container.width();
    const height = frameStyles.height || $container.height();

    const currentDimensions = this.state.dimensions;
    if (currentDimensions.width === width && currentDimensions.height === height) {
      return;
    }

    this.setState({
      dimensions: {
        width: width,
        height: height
      }
    });
  }

  renderFrameContents() {
    const doc = this.getContentDocument();

    if (doc.readyState === 'complete') {
      const { frameStyles = {}, containerStyles = {} } = this.props;
      const containerDimensions = {};
      if (frameStyles.width) {
        containerDimensions.width = frameStyles.width;
      }
      if (frameStyles.height) {
        containerDimensions.height = frameStyles.height;
      }

      if (!this.containerReady) {
        const $head = jQuery(doc.head);
        const $body = jQuery(doc.body);

        const $styles = jQuery(document).find('style').clone();
        const $container = jQuery('<div/>', {
          id: 'react_frame_container',
          css: {
            ...containerStyles,
            ...containerDimensions
          }
        });

        $head.html($styles);
        $body.html($container);

        this.containerReady = true;
      }

      const contents = React.createElement('div', containerDimensions, this.props.children);
      ReactDOM.render(contents, doc.body.firstChild);
    } else {
      setTimeout(this.renderFrameContents, 0);
    }
  }

  render() {
    return (
      <iframe ref="iframe"
              name={this.props.name}
              style={this.getFrameStyles()} />
    );
  }
}
