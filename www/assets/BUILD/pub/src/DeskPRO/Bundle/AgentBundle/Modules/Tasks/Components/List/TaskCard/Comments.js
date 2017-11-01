import PropTypes from 'prop-types';
import React from 'react';

export class Comments extends React.Component {

  static propTypes = {
    count: PropTypes.number
  };

  render() {
    return (
      <span className="dpwd--card-line-item">
        {Number(this.props.count) || 0} <i className="fa fa-comment" />
      </span>
    );
  }
}
