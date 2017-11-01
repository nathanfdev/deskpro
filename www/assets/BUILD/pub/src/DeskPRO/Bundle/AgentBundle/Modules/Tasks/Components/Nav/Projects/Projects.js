import PropTypes from 'prop-types';
import React from 'react';
import { Detached } from 'DeskPRO/Component/Detached';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ProjectForm } from './ProjectForm/ProjectForm';
import { ListItemContainer } from '../ListItemContainer';
import { pureRender } from 'Ampliflux';
import { addToCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import Immutable from 'immutable';

@pureRender
export class Projects extends React.Component {

  static propTypes = {
    projects:         PropTypes.object.isRequired,
    projectsCountMap: PropTypes.object.isRequired,
    dispatch:         PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = { project: null };
  }

  onCoverClick = (event) => {
    event.stopPropagation();
    event.nativeEvent.stopImmediatePropagation();

    if (event.target.className.indexOf('dpw-site-cover') !== -1) {
      this.onEdit(null, event);
    }
  };

  onEdit = (project, event) => {
    if (event) {
      event.preventDefault();
    }
    this.setState({ project });
  };

  onSubmit = (project) => {
    if (!project) return;

    this.setState({ project: null });
    this.props.dispatch(addToCollection('Project', 'all', Immutable.List([project])));
  };

  getCount(project) {
    const { projectsCountMap = {} } = this.props;
    return project && project.get('id') && projectsCountMap[project.get('id')] || 0;
  }

  renderProject = ([index, project]) => {
    const urlHash = `project-${project.get('id')}-${project.get('title')}`;

    return (
      <ListItemContainer key={index} urlHash={urlHash} listOptions={{ project: [project.get('id')] }}>
        <ListItem count={this.getCount(project)} onEdit={() => this.onEdit(project)}>
          <div part="label" className="section-list-title">
            <i className="fa fa-book" />
            <div className="cutted">
              {project.get('title')}
            </div>
          </div>
        </ListItem>
      </ListItemContainer>
    );
  };

  render() {
    const { project } = this.state;
    const { projects } = this.props;

    return (
      <Section>
        <SectionHeader>
          Projects &nbsp;
          <a href="#" onClick={() => this.onEdit(Immutable.fromJS({}))}>
            <i className="fa fa-plus" />
          </a>
        </SectionHeader>

        <ul>
          {projects.entrySeq().map(this.renderProject)}
        </ul>

        <Detached>
          {this.state.project
            ? <div className="dpw-site-cover with-popup" onClick={this.onCoverClick}>
                <ProjectForm project={project} tasksCount={this.getCount(project)} onSubmit={this.onSubmit} />
              </div>
            : null
          }
        </Detached>
      </Section>
    );
  }
}
