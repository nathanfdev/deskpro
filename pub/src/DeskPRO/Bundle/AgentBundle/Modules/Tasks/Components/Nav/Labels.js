import React from 'react';
import { Section, SectionHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class Labels extends React.Component {

  render() {
    return (
      <Section>
        <SectionHeader>Labels</SectionHeader>
        <div className="sidebar-label-list sidebar-list">
          <ul>
            <li>
              <span className="labelCharacter">A</span>
              <a href="#" className="item-label active">Label 1</a>
            </li>
            <li>
              <span className="labelCharacter">B</span>
              <a href="#" className="item-label">Label 2</a>
            </li>
          </ul>
        </div>
      </Section>
    );
  }
}
