import React, { PropTypes } from 'react';
import { Detached } from 'DeskPRO/Component/Detached';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ProjectForm } from './ProjectForm/ProjectForm';
import { ListItemContainer } from '../ListItemContainer';
import Immutable from 'immutable';

export class Projects extends React.Component {

  static propTypes = {
    projects:         PropTypes.object.isRequired,
    projectsCountMap: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      project:  null,
      projects: props.projects
    };
  }

  componentWillReceiveProps(props) {
    this.setState({
      projects: props.projects
    });
  }

  shouldComponentUpdate(props, state) {
    return !Immutable.is(this.state.project, state.project) || !Immutable.is(this.state.projects, state.projects);
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

    this.setState({
      project:  null,
      projects: this.state.projects.set(project.get('id'), project)
    });
  };

  getCount(project) {
    const { projectsCountMap = {} } = this.props;
    return project && project.get('id') && projectsCountMap[project.get('id')] || 0;
  }

  renderProject = (project, index) => {
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
    const { project, projects } = this.state;

    return (
      <Section>
        <SectionHeader>
          Projects &nbsp;
          <a href="#" onClick={() => this.onEdit(Immutable.fromJS({}))}>
            <i className="fa fa-plus" />
          </a>
        </SectionHeader>

        <ul>
          {projects.map(this.renderProject)}
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
