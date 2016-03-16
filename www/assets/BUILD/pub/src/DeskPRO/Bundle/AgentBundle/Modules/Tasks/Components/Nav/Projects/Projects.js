import React, { PropTypes } from 'react';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { ProjectForm } from './ProjectForm/ProjectForm';
import { ListItemContainer } from '../ListItemContainer';
import Immutable from 'immutable';

export class Projects extends React.Component {

  static propTypes = {
    projects: PropTypes.object.isRequired,
    projectsCount: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      project: null
    };
  }

  /**
   * hide project edit form
   * @param nextProps
   */
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

  onEdit = (project, event) => {
    event && event.preventDefault();
    this.setState({
      project: project
    });
  };

  render() {
    const { projects, projectsCount = [] } = this.props;
    const { project } = this.state;

    const countMap = [];
    projectsCount.forEach(projectCount => {
      countMap[projectCount.get('project_id')] = parseInt(projectCount.get('tasks_count'), 10);
    });

    return (
      <Section>
        <SectionHeader>
          Projects &nbsp;
          <a href="#" onClick={this.onEdit.bind(this, Immutable.fromJS({}))}>
            <i className="fa fa-plus"/>
          </a>
        </SectionHeader>

        <ul>
          {projects.map((project, index) =>
              <ListItemContainer key={index}
                                 urlHash={`project-${project.get('id')}-${project.get('title')}`}
                                 listOptions={{project: [project.get('id')]}}>

                <ListItem count={countMap[project.get('id')] || 0}
                          onEdit={this.onEdit.bind(this, project)}>

                  <div part="label">
                    <i className="fa fa-book"/> {project.get('title')}
                  </div>
                </ListItem>
              </ListItemContainer>
          )}
        </ul>

        <Detached isOpen={this.state.project}
                  positionTarget={this}
                  positionAt="right+5 top-6">

          <ClickOut onClickOut={this.onEdit.bind(this, null)}>
            <ProjectForm project={project}
                         tasksCount={project && project.get('id') && countMap[project.get('id')] || 0}/>
          </ClickOut>
        </Detached>
      </Section>
    );
  }
}
