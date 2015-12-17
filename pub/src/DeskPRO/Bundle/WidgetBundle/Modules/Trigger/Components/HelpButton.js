import React from 'react';

export class HelpButton extends React.Component {

  render() {
    return (
      <div>
        <a href="#" className="preemtive-button">
          <span className="state-button-text">Help</span>
        <span className="state-button-icon">
          <span>?</span>
        </span>
        </a>

        <a href="#" className="preemtive-button button-s">
          <span className="state-button-text">Help</span>
        <span className="state-button-icon">
          <span>?</span>
        </span>
        </a>

        <a href="#" className="preemtive-button button-l">
          <span className="state-button-text">Help</span>
        <span className="state-button-icon">
          <span>?</span>
        </span>
        </a>
      </div>
    );
  }
}
