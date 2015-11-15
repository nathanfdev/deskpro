import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Project } from './Project';
import { allProjectsSelector } from '../../../RecordStores/Selectors/projectSelectors';

@connect(state => ({
  projects: allProjectsSelector(state)
}))
export class ProjectContainer extends React.Component {

  static propTypes = {
    projects: PropTypes.object.isRequired,
    project: PropTypes.number
  };

  render() {
    const { project, projects } = this.props;

    return <Project project={projects.get(project)} />;
  }
}
