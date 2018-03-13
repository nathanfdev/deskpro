import PropTypes from 'prop-types';
import React from 'react';


class SimpleStat extends React.Component {

  static propTypes = {
    value:       PropTypes.string.isRequired,
    description: PropTypes.string,
  };

  render() {
    return (
      <div className="box">
        <div className="stat">
          <div className="stat-value">{this.props.value}</div>
          { this.props.description ? <div className="stat-description">{this.props.description}</div> : null }
        </div>
      </div>
    );
  }
}

export default SimpleStat;
