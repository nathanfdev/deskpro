import PropTypes from 'prop-types';
import React from 'react';
import { Section, SectionHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ListItemContainer } from '../ListItemContainer';
import { LabelItem } from './LabelItem';

export class Labels extends React.Component {

  static propTypes = {
    labelGroups: PropTypes.object.isRequired
  };

  render() {
    const { labelGroups } = this.props;

    return (
      <Section>
        <SectionHeader>Labels</SectionHeader>
        <div className="sidebar-label-list sidebar-list">
          <ul>
            {labelGroups.map((labels, char) =>
              <li key={char}>
                <span className="labelCharacter">{char}</span>

                {labels.map((label, index) =>
                  <ListItemContainer
                    key={index}
                    urlHash={`label-${label.get('label')}`}
                    listOptions={{ label: [label.get('label')] }}
                  >
                    <LabelItem label={label} />
                  </ListItemContainer>
                )}
              </li>
            )}
          </ul>
        </div>
      </Section>
    );
  }
}
