import React from 'react';
import { Th } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

export class Header extends React.Component {

  render() {
    return (
      <thead>
        <tr>
          <Th />
          <Th title="Title" sort="title" {...this.props} />
          <Th title="Project" sort="project" {...this.props} />
          <Th title="Due" sort="due" {...this.props} />
          <Th title="Assignee" sort="assignee" {...this.props} />
        </tr>
      </thead>
    );
  }
}
