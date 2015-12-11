import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { DropZoneOverlay } from './DropZoneOverlay';
import fileupload from 'blueimp-file-upload';
import $ from 'jquery';

export class DropZoneContainer extends React.Component {

  static propTypes = {
    input: PropTypes.node.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      overlay: false
    };
  }

  componentDidMount() {
    const document = window.widgetFrame.document;
    const overlayNode = ReactDOM.findDOMNode(this);
    const input = ReactDOM.findDOMNode(this.props.input);

    $(input).fileupload({
      url: '/path/to/upload/handler.json',
      dropZone: $(overlayNode)
    });

    $(document).on('dragover', this.onDragStarted);
    $(parent.window.document).on('dragover', this.onDragStarted);

    $(document).on('drop dragover', this.onDefaultDrop);
    $(parent.window.document).on('drop dragover', this.onDefaultDrop);
  }

  componentWillUnmount() {
    $('#fileupload').fileupload('destroy');

    $(document).off('dragover', this.onDragStarted);
    $(parent.window.document).off('dragover', this.onDragStarted);

    $(document).off('drop dragover', this.onDefaultDrop);
    $(parent.window.document).off('drop dragover', this.onDefaultDrop);
  }

  onDefaultDrop = e => {
    e.preventDefault();
  };

  onDragStarted = () => {
    if (!this.timeout) {
      this.setState({
        overlay: true
      });
    } else {
      clearTimeout(this.timeout);
    }

    this.timeout = setTimeout(this.onDragEnd, 100);
  };

  onDragEnd = () => {
    this.timeout = null;
    this.setState({
      overlay: false
    });
  };

  render() {
    return <DropZoneOverlay opened={this.state.overlay} />;
  }
}
