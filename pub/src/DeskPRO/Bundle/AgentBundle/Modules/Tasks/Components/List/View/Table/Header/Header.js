import React from 'react';
import { HeaderColumn } from './HeaderColumn';

export class Header extends React.Component {

  render() {
    return (
      <thead>
        <tr>
          <HeaderColumn />
          <HeaderColumn title="Title" order="title" {...this.props} />
          <HeaderColumn title="Project" order="project" {...this.props} />
          <HeaderColumn title="Due" order="due" {...this.props} />
          <HeaderColumn title="Assignee" order="assignee" {...this.props} />
        </tr>
      </thead>
    );
  }
}
