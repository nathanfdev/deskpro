import PropTypes from 'prop-types';
import React from 'react';

export class SubTasks extends React.Component {

  static propTypes = {
    current: PropTypes.number,
    total:   PropTypes.number
  };

  render() {
    const { current, total } = this.props;

    return (
      <span className="dpwd--card-line-item">
        <div>
          <span className="dpw--card-disc" /> {current}/{total} <i className="fa fa-folder-open" />
        </div>
      </span>
    );
  }
}
