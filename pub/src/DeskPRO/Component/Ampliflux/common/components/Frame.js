import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import jQuery from 'jquery';

export default class Frame extends React.Component {

  static propTypes = {
    style: PropTypes.object,
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
    const { style = {}, isVisible, positionMode } = this.props;

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

      ...style,
      ...position
    };
  }

  autoFrameDimensions() {
    const { style = {} } = this.props;
    const doc = this.getContentDocument();

    const $container = jQuery(doc.body.firstChild);
    const width = style.width || $container.width();
    const height = style.height || $container.height();

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
      const { style = {} } = this.props;
      const containerDimensions = {};
      if (style.width) {
        containerDimensions.width = style.width;
      }
      if (style.height) {
        containerDimensions.height = style.height;
      }

      if (!this.containerReady) {
        const $head = jQuery(doc.head);
        const $body = jQuery(doc.body);

        const $styles = jQuery(document).find('style').clone();
        const $container = jQuery('<div/>', {id: 'react_frame_container', css: containerDimensions});

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
    return <iframe ref="iframe" style={this.getFrameStyles()} />;
  }
}
