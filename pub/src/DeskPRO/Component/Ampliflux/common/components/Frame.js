import React from 'react';
import ReactDOM from 'react-dom';

export default class Frame extends React.Component {
  static propTypes = {
    style: React.PropTypes.object,
    head: React.PropTypes.node,
    isVisible: React.PropTypes.bool
  };

  constructor(rawProps) {
    let props = rawProps;

    super(props);

    this.state = {
      dims: { width: 0, height: 0 },
      isRendered: false,
      positionMode: rawProps.positionMode || 'bottom.right'
    };
  }

  getIframeNode() {
    if (!this.refs || !this.refs.iframe) {
      return null;
    }

    return ReactDOM.findDOMNode(this.refs.iframe) || null;
  }

  getIframeDocument() {
    const domNode = this.getIframeNode();
    if (!domNode || !domNode.contentDocument) {
      return null;
    }
    return domNode.contentDocument;
  }

  autoFrameDimentions() {
    const iframeDoc = this.getIframeDocument();

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

    if (this.state.dims.width == w && this.state.dims.height == h) {
      // same dims, no need to update
      return false;
    }

    this.setState({
      dims: { width: w, height: h }
    });

    return true;
  }

  setFrameDimentions(w, h) {
    if (this.state.dims.width == w && this.state.dims.height == h) {
      // same dims, no need to update
      return false;
    }

    this.setState({
      dims: { width: w, height: h }
    });

    return true;
  }

  render() {
    const props = {
      ...this.props,
      ref: "iframe",
      children: undefined
    };

    if (!props.style) {
      props.style = {};
    }

    props.style = {
      border: 'none',
      background: 'transparent',
      zIndex: 99999,
      width: this.state.dims.width || 0,
      height: this.state.dims.height || 0,
      position: 'fixed',
      ...props.style
    };

    if (!this.props.isVisible) {
      props.style.display = 'none';
    } else {
      props.style.display = 'block';
    }

    switch (this.state.positionMode) {
      case 'bottom.left':
        props.style.left = 0;
        props.style.bottom = 0;
        break;
      case 'bottom.right':
        props.style.right = 0;
        props.style.bottom = 0;
        break;
    }

    return (<iframe {...props} />);
  }

  componentDidMount() {
    this.renderFrameContents();
  }

  renderFrameContents() {
    if (this.state.isRendered) {
      return;
    }

    const doc = this.getIframeDocument();
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
      frameContainer.id = "react_frame_container";
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
      if (styleTag.styleSheet){
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

  componentDidUpdate() {
    this.renderFrameContents();
  }

  componentWillUnmount() {
    React.unmountComponentAtNode(ReactDOM.findDOMNode(this.refs.iframe).contentDocument.getElementById('react_frame_container'));
  }
}
