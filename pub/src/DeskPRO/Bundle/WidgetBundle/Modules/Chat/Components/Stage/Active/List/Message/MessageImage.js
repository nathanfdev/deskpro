import React from 'react';

export class MessageImage extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-message-content">
        <ul>
          <li className="dpdesignportal-message-asset">
            <div className="dpdesignportal-message-asset attachement-screen">
              <img alt="Sample Image" />
                <div className="dpdesignportal-message-asset-screen-controls">
                  <a href="#"><i className="fa fa-save"></i></a>
                  <a href="#"><i className="fa fa-expand"></i></a>
                  <a href="#"><i className="fa fa-times"></i></a>
                </div>
                <span className="dpdesignportal-message-asset-info">Noelle attached this photo</span>
                <span className="dpdesignportal-message-asset-cta">Click here to see the full image</span>
            </div>
          </li>
        </ul>
      </div>
    );
  }
}
