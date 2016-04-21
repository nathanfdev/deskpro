import React from 'react';
import { connect } from 'react-redux';
import { Projects } from './Projects';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { projectsCountSelector } from '../../../Selectors/nav';

@connect(state => ({
  projects: allSelectorFactory('Project')(state),
  projectsCount: projectsCountSelector(state)
}))
export class ProjectsContainer extends React.Component {

  render() {
    return <Projects {...this.props} />;
  }
}
