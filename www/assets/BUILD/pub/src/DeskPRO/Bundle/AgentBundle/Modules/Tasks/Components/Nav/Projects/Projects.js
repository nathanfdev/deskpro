import React, { PropTypes } from 'react';
import { Detached } from 'DeskPRO/Component/Detached';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ProjectForm } from './ProjectForm/ProjectForm';
import { ListItemContainer } from '../ListItemContainer';
import Immutable from 'immutable';

export class Projects extends React.Component {

  static propTypes = {
    projects:      PropTypes.object.isRequired,
    projectsCount: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      project: null
    };

    this.countMap = [];
  }

  componentWillReceiveProps(nextProps) {
    if (!this.state.project) return;
    const id = this.state.project.get('id');

    if (id) {
      // on edit
      if (nextProps.projects.get(id)) return;
    } else {
      // on create
      if (nextProps.projects.size === this.props.projects.size) return;
    }

    this.setState({
      project: null
    });
  }

  componentWillUpdate() {
    const { projectsCount = [] } = this.props;
    this.countMap = [];
    projectsCount.forEach(projectCount => {
      this.countMap[projectCount.get('project_id')] = parseInt(projectCount.get('tasks_count'), 10);
    });
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

  getCount(project) {
    return project && project.get('id') && this.countMap[project.get('id')] || 0;
  }

  renderProject = (project, index) => {
    const urlHash = `project-${project.get('id')}-${project.get('title')}`;

    return (
      <ListItemContainer key={index}
        urlHash={urlHash}
        listOptions={{ project: [project.get('id')] }}>

        <ListItem count={this.countMap[project.get('id')] || 0} onEdit={() => this.onEdit(project)}>
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
    const { projects } = this.props;
    const { project } = this.state;

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
                <ProjectForm project={project} tasksCount={this.getCount(project)} onSubmit={() => this.onEdit(null)} />
              </div>
            : null
          }
        </Detached>
      </Section>
    );
  }
}
