import React, { PropTypes } from 'react';
import { Section, SectionHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from '../ListItemContainer';
import { LabelItem } from './LabelItem';

export class Labels extends React.Component {

  static propTypes = {
    labels: PropTypes.object.isRequired
  };

  renderLabel(label, index) {
    return (
      <ListItemContainer key={index}
                         label={`label-${label.get('label')}`}>

        <LabelItem label={label} onClick={() => {}} />
      </ListItemContainer>
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
