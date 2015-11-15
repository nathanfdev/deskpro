import React, { PropTypes } from 'react';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { ProjectFormContainer } from './ProjectForm/ProjectFormContainer';
import { ListItemContainer } from '../ListItemContainer';

export class Projects extends React.Component {

  static propTypes = {
    projects: PropTypes.object.isRequired,
    projectsCount: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      editProject: null,
      formOpened: false
    };
  }

  onEdit = project => {
    this.setState({
      editProject: project,
      formOpened: true
    });
  };

  onOpenForm = event => {
    event.preventDefault();
    this.setState({
      formOpened: true
    });
  };

  onCloseForm = () => {
    this.setState({
      editProject: null,
      formOpened: false
    });
  };

  render() {
    const { projects, projectsCount = [] } = this.props;
    const countMap = [];
    projectsCount.forEach(projectCount => {
      countMap[projectCount.get('project_id')] = parseInt(projectCount.get('tasks_count'), 10);
    });

    return (
      <Section>
        <SectionHeader>
          Projects &nbsp;
          <a href="#" onClick={this.onOpenForm}>
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
                  <i className="fa fa-book" /> {project.get('title')}
                </div>
              </ListItem>
            </ListItemContainer>
          )}
        </ul>

        <Detached isOpen={this.state.formOpened}
                  positionTarget={this}
                  positionAt="right+5 top-6">

          <ClickOut onClickOut={this.onCloseForm}>
            <ProjectFormContainer project={this.state.editProject} />
          </ClickOut>
        </Detached>
      </Section>
    );
  }
}
