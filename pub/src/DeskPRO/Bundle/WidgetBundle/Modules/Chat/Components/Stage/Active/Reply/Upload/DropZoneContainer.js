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

    $(document).bind('drop dragover', e => {
      e.preventDefault();
    });

    $(parent.window.document).bind('drop dragover', e => {
      e.preventDefault();
    });
  }

  componentWillUnmount() {
    $('#fileupload').fileupload('destroy');
  }

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
