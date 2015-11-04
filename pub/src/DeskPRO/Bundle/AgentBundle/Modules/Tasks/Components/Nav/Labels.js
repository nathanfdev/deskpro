import React, { PropTypes } from 'react';
import { Section, SectionHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class Labels extends React.Component {

  static propTypes = {
    labels: PropTypes.object.isRequired
  };

  renderLabel(label, index) {
    const name = label.get('label');
    const char = name && name.substr(0, 1).toUpperCase();

    return (
      <li key={index}>
        <span className="labelCharacter">{char}</span>
        <a href="#" className="item-label active">{name}</a>
      </li>
    );
  }

  render() {
    return (
      <Section>
        <SectionHeader>Labels</SectionHeader>
        <div className="sidebar-label-list sidebar-list">
          <ul>
            {this.props.labels.map((label, index) => this.renderLabel(label, index))}
          </ul>
        </div>
      </Section>
    );
  }
}
