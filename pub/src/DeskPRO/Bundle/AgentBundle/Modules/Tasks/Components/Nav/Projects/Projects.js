import React from 'react';
import { Section, SectionHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { ProjectFormContainer } from './ProjectForm/ProjectFormContainer';
import { ProjectItem } from './ProjectItem';

export class Projects extends React.Component {

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
          <ProjectItem label="Example Project" count={0} />
          <ProjectItem label="Example Project 2" count={2} />
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
