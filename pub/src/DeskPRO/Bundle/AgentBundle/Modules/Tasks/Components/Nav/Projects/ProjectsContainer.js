import React from 'react';
import { connect } from 'react-redux';
import { Projects } from './Projects';
import { allProjectsSelector } from '../../../RecordStores/Selectors/projectSelectors';

@connect(state => ({
  projects: allProjectsSelector(state)
}))
export class ProjectsContainer extends React.Component {

  render() {
    return <Projects {...this.props} />;
  }
}
