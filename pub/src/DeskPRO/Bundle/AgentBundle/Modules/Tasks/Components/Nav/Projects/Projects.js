import React, { PropTypes } from 'react';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { Section, SectionHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { ProjectFormContainer } from './ProjectForm/ProjectFormContainer';
import { ListItemContainer } from '../ListItemContainer';

export class Projects extends React.Component {

  static propTypes = {
    onApplyListParams: PropTypes.func.isRequired,
    projects: PropTypes.object.isRequired
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

  onOpenForm = () => {
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

  renderItem(project, index) {
    const filter = {project: project.get('id')};

    return (
      <ListItemContainer key={index}
                         label={`project-${project.get('id')}-${project.get('title')}`}
                         count={project.get('remaining')}
                         onEdit={this.onEdit.bind(this, project)}
                         onClick={this.props.onApplyListParams.bind(this, filter)}
                         listOptions={filter}>

        <div part="label">
          <i className="fa fa-book" /> {project.get('title')}
        </div>
      </ListItemContainer>
    );
  }

  render() {
    return (
      <Section>
        <SectionHeader>
          Projects &nbsp;
          <a href="#" onClick={this.onOpenForm}>
            <i className="fa fa-plus"/>
          </a>
        </SectionHeader>

        <ul>
          {this.props.projects.map((project, index) => this.renderItem(project, index))}
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
