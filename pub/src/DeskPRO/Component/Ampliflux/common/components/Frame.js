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
    React.unmountComponentAtNode(ReactDOM.findDOMNode(this.refs.iframe).contentDocument.getElementById('react_frame_container'));
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

  setFrameDimentions(w, h) {
    if (this.state.dims.width === w && this.state.dims.height === h) {
      // same dims, no need to update
      return false;
    }

    this.setState({
      dims: { width: w, height: h }
    });

    return true;
  }

  autoFrameDimentions() {
    const iframeDoc = this.getFrameDocument();

    if (!iframeDoc) {
      return false;
    }

    let w = Math.max(iframeDoc.body.firstChild.clientWidth, iframeDoc.body.firstChild.offsetWidth);
    const h = Math.max(iframeDoc.body.firstChild.clientHeight, iframeDoc.body.firstChild.offsetHeight);

    // todo must be better way to calculate width?
    if (!w) {
      const tags = iframeDoc.getElementsByTagName('*');
      for (let i = 0; i < tags.length; i++) {
        w += Math.max(tags[i].clientWidth, tags[i].offsetWidth);
      }
    }

    if (this.state.dims.width === w && this.state.dims.height === h) {
      // same dims, no need to update
      return false;
    }

    this.setState({
      dims: { width: w, height: h }
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
      var contents = React.createElement('div',
        undefined,
        this.props.head,
        this.props.children
      );

      const frameContainer = doc.createElement('div');
      frameContainer.id = 'react_frame_container';
      doc.body.appendChild(frameContainer);

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

      ReactDOM.render(contents, frameContainer);
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
