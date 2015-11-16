import React from 'react';
import { Th } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

export class Header extends React.Component {

  render() {
    return (
      <thead>
        <tr>
          <Th />
          <Th label="Title" value="title" {...this.props} />
          <Th label="Project" value="project" {...this.props} />
          <Th label="Due" value="due" {...this.props} />
          <Th label="Assignee" value="assignee" {...this.props} />
        </tr>
      </thead>
    );
  }
}
