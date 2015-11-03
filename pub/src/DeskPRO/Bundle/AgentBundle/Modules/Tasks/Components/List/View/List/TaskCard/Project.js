import React, { PropTypes } from 'react';

export class Project extends React.Component {

  static propTypes = {
    project: PropTypes.string
  };

  render() {
    return (
      <span>
        <span className="dpw--card-disc"/>
        <span className="dpwd--card-line-item">
          <i className="fa fa-book"/> {this.props.project}
        </span>
      </span>
    );
  }
}
