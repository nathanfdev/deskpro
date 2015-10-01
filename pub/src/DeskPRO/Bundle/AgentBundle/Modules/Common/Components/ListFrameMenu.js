import React from 'react';

export default class ListFrameMenu extends React.Component {

  render() {
    return (
      <div className="control-bar">
        <div className="ticket-controls-bulk-editing">
          <div className="dpwd-navigation-dropdown-top-row">
            <ul className="dpwd-navigation-dropdown-top-row-main-list">
              {this.props.children}
            </ul>
          </div>
        </div>
      </div>
    );
  }
}
