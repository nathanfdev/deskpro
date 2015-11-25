import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';

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
      dims: { width: 0, height: 0 },
      isRendered: false,
      positionMode: props.positionMode || 'bottom.right'
    };
  }

  componentDidMount() {
    this.renderFrameContents();
  }

  componentDidUpdate() {
    this.renderFrameContents();
  }

  componentWillUnmount() {
    const domNode = ReactDOM.findDOMNode(this.refs.iframe);
    React.unmountComponentAtNode(domNode.contentDocument.getElementById('react_frame_container'));
  }

  getFrameNode() {
    if (!this.refs || !this.refs.iframe) {
      return null;
    }

    return ReactDOM.findDOMNode(this.refs.iframe) || null;
  }

  getFrameDocument() {
    const domNode = this.getFrameNode();
    if (!domNode || !domNode.contentDocument) {
      return null;
    }
    return domNode.contentDocument;
  }

  autoFrameDimentions() {
    const document = this.getFrameDocument();
    if (!document) {
      return false;
    }

    const firstChild = document.body.firstChild;
    let width = Math.max(firstChild.clientWidth, firstChild.offsetWidth);
    const height = Math.max(firstChild.clientHeight, firstChild.offsetHeight);

    // todo must be better way to calculate width?
    if (!width) {
      const tags = document.getElementsByTagName('*');
      for (let i = 0; i < tags.length; i++) {
        width += Math.max(tags[i].clientWidth, tags[i].offsetWidth);
      }
    }

    const dimentions = this.state.dims;
    if (dimentions.width === width && dimentions.height === height) {
      // same dims, no need to update
      return false;
    }

    this.setState({
      dims: {
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

    const doc = this.getFrameDocument();
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
    const props = this.props;
    const frameProps = {
      ...props,
      ref: 'iframe',
      children: undefined
    };

    const overrideStyle = frameProps.style || {};
    frameProps.style = {
      border: 'none',
      background: 'transparent',
      zIndex: 99999,
      width: this.state.dims.width || 0,
      height: this.state.dims.height || 0,
      position: 'fixed',

      ...overrideStyle
    };

    if (!props.isVisible) {
      frameProps.style.display = 'none';
    } else {
      frameProps.style.display = 'block';
    }

    switch (this.state.positionMode) {
      case 'bottom.left':
        frameProps.style.left = 0;
        frameProps.style.bottom = 0;
        break;
      case 'bottom.right':
        frameProps.style.right = 0;
        frameProps.style.bottom = 0;
        break;
      default:
        break;
    }

    return <iframe {...frameProps} />;
  }
}
