import React, { PropTypes } from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ListItemContainer } from '../ListItemContainer';
import jQuery from 'jquery';

export class Groups extends React.Component {

  static propTypes = {
    groupsOptions: PropTypes.object.isRequired,
    groupsCount: PropTypes.object.isRequired
  };

  render() {
    const { groupsOptions, groupsCount } = this.props;

    return (
      <Section>
        <SectionHeader>Tasks</SectionHeader>
        <ul>
          {jQuery.map(groupsOptions, (params, type) =>
            <ListItemContainer key={type}
                               urlHash={params.label}
                               listOptions={params.filter}>

              <ListItem count={groupsCount.get(type)}
                        label={params.label} />
            </ListItemContainer>
          )}
        </ul>
      </Section>
    );
  }
}
