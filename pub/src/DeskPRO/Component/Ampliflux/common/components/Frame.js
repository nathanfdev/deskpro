import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import jQuery from 'jquery';

export default class Frame extends React.Component {

  static propTypes = {
    style: PropTypes.object,
    head: PropTypes.node,
    isVisible: PropTypes.bool,
    positionMode: PropTypes.string,
    children: PropTypes.any
  };

  constructor(props) {
    super(props);
    this.state = {
      dimensions: {
        width: 0,
        height: 0
      },
      isRendered: false
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

    let position = {};
    switch (positionMode) {
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
    const document = this.getContentDocument();
    if (!document) {
      return false;
    }

    const $container = jQuery(document.body.firstChild);
    const width = $container.width();
    const height = $container.height();

    const dimensions = this.state.dimensions;
    if (dimensions.width === width && dimensions.height === height) {
      // same dimensions, no need to update
      return false;
    }

    this.setState({
      dimensions: {
        width: width,
        height: height
      }
    });

    return true;
  }

  renderFrameContents() {
    if (this.state.isRendered) {
      return;
    }

    const doc = this.getContentDocument();
    if (!doc) {
      return;
    }

    if (doc && doc.readyState === 'complete') {
      const { head, children } = this.props;
      const contents = React.createElement('div', undefined, head, children);

      const container = doc.createElement('div');
      container.id = 'react_frame_container';
      doc.body.appendChild(container);

      // This is copying CSS from the current page
      // into the iframe
      // TODO this need some xbrowser testing
      let css = [];
      const styles = document.getElementsByTagName('style');
      for (let i = 0; i < styles.length; i++) {
        css.push(styles[i].innerHTML);
      }

      css = css.join("\n");

      const styleTag = doc.createElement('style');
      styleTag.type = 'text/css';
      if (styleTag.styleSheet) {
        styleTag.styleSheet.cssText = css;
      } else {
        styleTag.appendChild(doc.createTextNode(css));
      }

      doc.body.appendChild(styleTag);

      ReactDOM.render(contents, container);
      this.setState({isRendered: true});
    } else {
      setTimeout(this.renderFrameContents, 0);
    }
  }

  render() {
    return <iframe ref="iframe" style={this.getFrameStyles()} />;
  }
}
