import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';

class SimpleFrame extends React.Component {

  static propTypes = {
    name:        PropTypes.string,
    frameStyles: PropTypes.object,
    isVisible:   PropTypes.bool,
    content:     PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = {
      dimensions: {
        width:  0,
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
    ReactDOM.unmountComponentAtNode(this.getContentDocument().body);
  }

  getContentDocument() {
    return this.iframe.contentDocument;
  }

  getFrameStyles() {
    const { frameStyles = {}, isVisible } = this.props;
    const { dimensions } = this.state;

    return {
      border:     'none',
      background: 'transparent',
      zIndex:     99999,
      width:      dimensions.width,
      height:     dimensions.height,
      position:   'fixed',
      display:    isVisible ? 'block' : 'none',

      ...frameStyles
    };
  }

  autoFrameDimensions() {
    const { frameStyles = {} } = this.props;
    const { dimensions } = this.state;

    const doc = this.getContentDocument();

    const $container = $(doc.body.firstChild);
    const width = frameStyles.width || $container.outerWidth();
    const height = frameStyles.height || $container.outerHeight();

    if (dimensions.width === width && dimensions.height === height) {
      return;
    }

    this.setState({
      dimensions: { width, height }
    });
  }

  renderFrameContents() {
    const doc = this.getContentDocument();

    const { content } = this.props;
    doc.open();
    doc.write(content);
    doc.close();
  }

  render() {
    const { name } = this.props;
    const styles = this.getFrameStyles();

    return (
      <iframe
        ref={(c) => { this.iframe = c; }}
        name={name}
        style={styles}
      />
    );
  }
}
export default SimpleFrame;
