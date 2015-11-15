import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { allProjectsSelector } from '../../../RecordStores/Selectors/projectSelectors';

@connect(state => ({
  projects: allProjectsSelector(state)
}))
export class ProjectContainer extends React.Component {

  static propTypes = {
    projects: PropTypes.object.isRequired,
    project: PropTypes.number,
    children: PropTypes.node.isRequired
  };

  render() {
    const { project, projects } = this.props;

    const child = this.props.children;
    const childProps = child.props;

    return React.cloneElement(child, {
      ...childProps,
      project: projects.get(project)
    });
  }
}
