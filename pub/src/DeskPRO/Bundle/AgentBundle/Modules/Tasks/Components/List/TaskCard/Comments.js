import React, { PropTypes } from 'react';

export class Comments extends React.Component {

  static propTypes = {
    count: PropTypes.number
  };

  render() {
    return (
      <span className="dpwd--card-line-item">
        {Number(this.props.count) || 0} <i className="fa fa-comment"/>
      </span>
    );
  }
}
