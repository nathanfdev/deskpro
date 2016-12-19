import React from 'react';
import { connect } from 'react-redux';
import { Projects } from './Projects';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { projectsCountMapSelector } from '../../../Selectors/nav';

@connect(state => ({
  projects:         allSelectorFactory('Project')(state),
  projectsCountMap: projectsCountMapSelector(state)
}))
export class ProjectsContainer extends React.Component {

  render() {
    return <Projects {...this.props} />;
  }
}
