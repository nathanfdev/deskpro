import React, { PropTypes } from 'react';

export class CardProject extends React.Component {

  static propTypes = {
    project: PropTypes.object
  };

  render() {
    const { project } = this.props;

    return (
      <span>
        <span className="dpw--card-disc"/>
        <span className="dpwd--card-line-item">
          <i className="fa fa-book"/> {project && project.get('title')}
        </span>
      </span>
    );
  }
}
