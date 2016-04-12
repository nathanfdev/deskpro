import React, { PropTypes } from 'react';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { ProjectFormContainer } from './ProjectForm/ProjectFormContainer';
import { ListItemContainer } from '../ListItemContainer';
import Immutable from 'immutable';

export class Projects extends React.Component {

  static propTypes = {
    projects:         PropTypes.object.isRequired,
    projectsCountMap: PropTypes.array.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      project: null
    };
  }

  onNewProject = event => {
    event.preventDefault();
    this.setState({
      project: Immutable.fromJS({})
    });
  };

  onEdit = project => {
    this.setState({ project });
  };

  onCloseEditing = () => {
    this.setState({
      project: null
    });
  };

  renderProjects() {
    const { projects, projectsCountMap } = this.props;

    return (
      <ul>
        {projects.map((project, index) =>
          <ListItemContainer
            key={index}
            urlHash={`project-${project.get('id')}-${project.get('title')}`}
            listOptions={{ project: [project.get('id')] }}
            >
            <ListItem count={projectsCountMap[project.get('id')] || 0} onEdit={() => this.onEdit(project)}>
              <div part="label">
                <i className="fa fa-book" /> {project.get('title')}
              </div>
            </ListItem>
          </ListItemContainer>
        )}
      </ul>
    );
  }

  render() {
    const { project } = this.state;

    return (
      <Section>
        <SectionHeader>
          Projects &nbsp;
          <a href="#" onClick={this.onNewProject}>
            <i className="fa fa-plus" />
          </a>
        </SectionHeader>

        {this.renderProjects()}

        <Detached
          isOpen={this.state.project}
          positionTarget={this}
          positionAt="right+5 top-6"
          collision="fit"
          >
          <ClickOut onClickOut={this.onCloseEditing}>
            <ProjectFormContainer project={project} />
          </ClickOut>
        </Detached>
      </Section>
    );
  }
}
