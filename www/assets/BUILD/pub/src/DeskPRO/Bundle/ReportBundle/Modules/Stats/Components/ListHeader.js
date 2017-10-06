import React from 'react';

class ListHeader extends React.Component {

  render() {
    return (
      <div className="big-list-of-stats-filters">
        <div className="bucket filter-title">
          <div className="box">
            <input type="text" placeholder="Filter stats by name" />
          </div>
        </div>

        <div className="bucket filter-label" style={{ position: 'relative' }}>
          <a className="link-pointer select">Select labels: <i className="fa fa-caret-down" /></a>
        </div>
        <a className="bucket add button"><i className="fa fa-plus" /> ADD</a>
      </div>
    );
  }
}

export default ListHeader;
