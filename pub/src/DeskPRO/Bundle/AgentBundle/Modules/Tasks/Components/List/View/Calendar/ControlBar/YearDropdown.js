import React, { PropTypes } from 'react';

export class YearDropdown extends React.Component {

  static propTypes = {
    date: PropTypes.object.isRequired,
    onChange: PropTypes.func.isRequired
  };

  render() {
    return (
      <div className="dpw-value-dropdown">
        <ul>
          <li><a href="#">2010</a></li>
          <li><a href="#">2011</a></li>
          <li><a href="#">2012</a></li>
          <li><a href="#">2013</a></li>
          <li><a href="#">2014</a></li>
          <li><a href="#">2015</a></li>
        </ul>
      </div>
    );
  }
}
