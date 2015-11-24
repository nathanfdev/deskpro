import _ from "lodash";
import $ from "jquery";
import React from "react";

export default class PortalAttach extends React.Component {
  render() {
    return (
       <div className="new-ticket-attachements">
          <a href="#" className="attach-file">
            <i className="fa fa-upload" />
            <span className="text">Drag a file in here or</span>
            <span className="fake-button">Choose a file</span>
          </a>

          <div className="title">Attached files:</div>

          <ul>
            <li>
                {/** these icons will be in the payload from the server (HTML) **/}
              <a href="#"><i className="fa fa-file-text-o" /> Screen Shot 2014-09-30 at 6.14.15 PM.png</a><span className="file-size">(245kb)</span>
              <a href="#" className="remove-attachement"><i className="fa fa-times" />Remove</a>
            </li>

            <li>
              <a href="#"><i className="fa fa-file-text-o" /> a_doc_file.doc</a><span className="file-size">(84kb)</span>
              <a href="#" className="remove-attachement"><i className="fa fa-times" />Remove</a>
            </li>
          </ul>
      </div>
    );
  }
}
