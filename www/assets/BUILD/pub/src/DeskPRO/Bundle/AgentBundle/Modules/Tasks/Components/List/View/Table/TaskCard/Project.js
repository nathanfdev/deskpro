import React, { PropTypes } from 'react';

export class Project extends React.Component {

  static propTypes = {
    project: PropTypes.object
  };

  render() {
    const { project } = this.props;

    return (
      <span>
        {project && project.get('title')}
      </span>
    );
  }
}
