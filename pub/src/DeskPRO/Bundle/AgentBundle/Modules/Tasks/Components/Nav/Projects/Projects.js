import React, { PropTypes } from 'react';
import { Section, SectionHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { ProjectFormContainer } from './ProjectForm/ProjectFormContainer';
import { ProjectItem } from './ProjectItem';

export class Projects extends React.Component {

  static propTypes = {
    projects: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      formOpened: false
    };
  }

  openForm = () => {
    this.setState({
      formOpened: true
    });
  };

  closeForm = () => {
    this.setState({
      formOpened: false
    });
  };

  onEdit = () => {
    this.setState({
      formOpened: true
    });
  };

  render() {
    return (
      <Section>
        <SectionHeader>
          Projects &nbsp;
          <a href="#" onClick={this.openForm}>
            <i className="fa fa-plus"/>
          </a>
        </SectionHeader>

        <ul>
          {this.props.projects.map((project, index) =>
            <ProjectItem project={project} key={index} onEdit={this.onEdit}>
              <div part="label">
                <i className="fa fa-book" /> {project.get('title')}
              </div>
            </ProjectItem>
          )}
        </ul>

        <Detached isOpen={this.state.formOpened}
                  positionTarget={this}
                  positionAt="right+5 top-6">

          <ClickOut onClickOut={this.closeForm}>
            <ProjectFormContainer />
          </ClickOut>
        </Detached>
      </Section>
    );
  }
}
