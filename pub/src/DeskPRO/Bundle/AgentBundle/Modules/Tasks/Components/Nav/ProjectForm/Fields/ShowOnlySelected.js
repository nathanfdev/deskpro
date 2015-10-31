import React from 'react';

export class ShowOnlySelected extends React.Component {

  render() {
    return (
      <div className="dpw-popup-content-item-show-only-selected">
        <a href="#" className="checkbox-link'">
          <span>Show only Selected</span>
          <span className="dpw--checkbox-boxy"><i className="fa fa-check" /></span>
        </a>
      </div>
    );
  }
}
