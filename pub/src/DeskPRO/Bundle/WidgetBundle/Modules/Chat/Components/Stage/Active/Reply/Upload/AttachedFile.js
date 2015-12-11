import React, { PropTypes } from 'react';

export class AttachedFile extends React.Component {

  static propTypes = {
    fileId: PropTypes.number,
    name: PropTypes.string,
    onRemove: PropTypes.func
  };

  onRemove = event => {
    event.preventDefault();

    const { fileId, onRemove } = this.props;
    onRemove(fileId);
  };

  render() {
    return (
      <div className="dpdesignportal-chat-form-attached-file">
        <div className="dpdesignportal-chat-form-attached-file-icon">
          <i className="fa fa-file-pdf-o"></i>
        </div>
        <div className="attached-file-title">{this.props.name}</div>
        <a href="#" className="dpdesignportal-chat-form-attached-file-remove" onClick={this.onRemove}>
          <i className="fa fa-times-circle"></i>
        </a>
      </div>
    );
  }
}
