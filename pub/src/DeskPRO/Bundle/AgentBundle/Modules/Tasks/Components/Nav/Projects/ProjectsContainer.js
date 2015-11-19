import React from 'react';
import { connect } from 'react-redux';
import { Projects } from './Projects';
import { allProjectsSelector } from '../../../RecordStores/Selectors/projectSelectors';
import { projectsCountSelector } from '../../../Selectors/nav';

@connect(state => ({
  projects: allProjectsSelector(state),
  projectsCount: projectsCountSelector(state)
}))
export class ProjectsContainer extends React.Component {

  render() {
    return <Projects {...this.props} />;
  }
}
