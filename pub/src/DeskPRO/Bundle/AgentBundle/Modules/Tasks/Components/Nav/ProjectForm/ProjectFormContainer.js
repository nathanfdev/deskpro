import React from 'react';
import { connect } from 'react-redux';
import { ProjectForm } from './ProjectForm';

@connect()
export class ProjectFormContainer extends React.Component {

  render() {
    return (
      <ProjectForm {...this.props} />
    );
  }
}
