import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';

export class Frame extends React.Component {

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
    const dimensions = this.state.dimensions;

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
      width: dimensions.width,
      height: dimensions.height,
      position: 'fixed',
      display: isVisible ? 'block' : 'none',

      ...frameStyles,
      ...position
    };
  }

  autoFrameDimensions() {
    const { frameStyles = {} } = this.props;
    const doc = this.getContentDocument();

    const $container = $(doc.body.firstChild);
    const width = frameStyles.width || $container.outerWidth();
    const height = frameStyles.height || $container.outerHeight();

    const dimensions = this.state.dimensions;
    if (dimensions.width === width && dimensions.height === height) {
      return;
    }

    this.setState({
      dimensions: { width, height }
    });
  }

  renderFrameContents() {
    const doc = this.getContentDocument();
    const { frameStyles = {}, containerStyles = {} } = this.props;
    const containerDimensions = {};

    if (frameStyles.width) {
      containerDimensions.width = frameStyles.width;
    }
    if (frameStyles.height) {
      containerDimensions.height = frameStyles.height;
    }

    const frameContainerStyles = {
      ...containerStyles,
      ...containerDimensions
    };

    if (doc.readyState === 'complete') {
      if (!this.containerReady) {
        const $head = $(doc.head);
        const $body = $(doc.body);

        const $styles = $(document).find('style').clone();
        const $container = $('<div/>', {
          id: 'react_frame_container',
          css: frameContainerStyles
        });

        $head.html($styles);
        $body.html($container);

        this.containerReady = true;
      } else {
        const $container = $(doc.body.firstChild);
        if (Object.keys(frameContainerStyles).length) {
          $container.css(frameContainerStyles);
        } else {
          $container.removeAttr('style');
        }
      }

      const contents = React.createElement('div', containerDimensions, this.props.children);
      ReactDOM.render(contents, doc.body.firstChild);
    } else {
      setTimeout(() => this.renderFrameContents(), 0);
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
