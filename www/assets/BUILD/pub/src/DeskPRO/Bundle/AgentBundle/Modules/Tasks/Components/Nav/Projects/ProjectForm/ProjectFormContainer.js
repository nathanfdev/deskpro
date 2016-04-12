import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { ProjectForm } from './ProjectForm';
import { projectsCountMapSelector } from '../../../../Selectors/nav';

@connect(state => ({
  projectsCountMap: projectsCountMapSelector(state)
}))
export class ProjectFormContainer extends React.Component {

  static propTypes = {
    project:          PropTypes.object,
    projectsCountMap: PropTypes.array.isRequired
  };

  render() {
    const { project, projectsCountMap } = this.props;
    const taskCount = project && project.get('id') && projectsCountMap[project.get('id')] || 0;

    return <ProjectForm {...this.props} tasksCount={taskCount} />;
  }
}
