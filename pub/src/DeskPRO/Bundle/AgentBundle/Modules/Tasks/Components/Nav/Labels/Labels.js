import React, { PropTypes } from 'react';
import { Section, SectionHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from '../ListItemContainer';
import { LabelItem } from './LabelItem';

export class Labels extends React.Component {

  static propTypes = {
    labels: PropTypes.object.isRequired
  };

  render() {
    return (
      <Section>
        <SectionHeader>Labels</SectionHeader>
        <div className="sidebar-label-list sidebar-list">
          <ul>
            {this.props.labels.map((label, index) =>
              <ListItemContainer key={index}
                                 label={`label-${label.get('label')}`}
                                 listOptions={{label: label.get('id')}}>

                <LabelItem label={label} />
              </ListItemContainer>
            )}
          </ul>
        </div>
      </Section>
    );
  }
}
