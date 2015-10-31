import React from 'react';

export class QuickFilter extends React.Component {

  render() {
    return (
      <div className="dpw-quick-filter">
        <div className="dpw-quick-filter-container">
          <div className="dpw-quick-filter-icon"><i className="fa fa-filter" /></div>
          <input type="text" placeholder="Quick Filter" />
          <span className="dpw-quick-filter-clear-link"><i className="fa fa-times-circle"></i></span>
        </div>
      </div>
    );
  }
}
