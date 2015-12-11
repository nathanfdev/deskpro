import React from 'react';
import ReactDOM from 'react-dom';
import { DropZoneOverlay } from './DropZoneOverlay';
import fileupload from 'blueimp-file-upload';

export class DropZoneContainer extends React.Component {

  componentDidMount() {
    const node = ReactDOM.findDOMNode(this);
    const overlayNode = ReactDOM.findDOMNode(this.refs.overlay);

    fileupload(node, {dropZone: overlayNode});
  }

  render() {
    return (
      <div>
        <DropZoneOverlay ref="overlay" />
      </div>
    );
  }
}
