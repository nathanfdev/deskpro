import React from 'react';

export class AttachedFile extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-chat-form-attached-file">
        <div className="dpdesignportal-chat-form-attached-file-icon">
          <i className="fa fa-file-pdf-o"></i>
        </div>
        <div className="attached-file-title">file_name_lorem_ipsum.pdf</div>
        <a href="#" className="dpdesignportal-chat-form-attached-file-remove">
          <i className="fa fa-times-circle"></i>
        </a>
      </div>
    );
  }
}
