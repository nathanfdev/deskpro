import React from 'react';
import ReactDOM from 'react-dom';
import { DropZoneOverlay } from './DropZoneOverlay';
import fileupload from 'blueimp-file-upload';
import $ from 'jquery';

export class DropZoneContainer extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      overlay: false
    };
  }

  componentDidMount() {
    const document = window.widgetFrame.document;
    const overlayNode = ReactDOM.findDOMNode(this.refs.overlay);

    $('#fileupload').fileupload({
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

    this.timeout = setTimeout(() => {
      this.timeout = null;
      this.setState({
        overlay: false
      });
    }, 100);
  };

  render() {
    return (
      <div>
        <DropZoneOverlay opened={this.state.overlay} ref="overlay" />
      </div>
    );
  }
}
