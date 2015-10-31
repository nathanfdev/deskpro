import React from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { ProjectForm } from './ProjectForm';

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
          <ListItem count={0}>
            <div part="label">
              <i className="fa fa-book" /> Example Project
            </div>
          </ListItem>
          <ListItem count={2}>
            <div part="label">
              <i className="fa fa-book" /> Example Project2
            </div>
          </ListItem>
        </ul>

        <Detached isOpen={this.state.formOpened}
                  positionTarget={this}>

          <ClickOut onClickOut={this.closeForm}>
            <ProjectForm />
          </ClickOut>
        </Detached>
      </Section>
    );
  }
}
