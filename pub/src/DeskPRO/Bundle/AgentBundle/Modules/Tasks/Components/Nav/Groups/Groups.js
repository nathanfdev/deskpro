import React, { PropTypes } from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from '../ListItemContainer';
import jQuery from 'jquery';

const groupOptions = {
  my: {filter: {agents: ['me']}, label: 'My Tasks'},
  team: {filter: {teams: ['me']}, label: 'My Team Tasks'},
  department: {filter: {departments: ['me']}, label: 'My Department Tasks'},
  delegated: {filter: {agents: ['not_me'], creator: 'me'}, label: 'My Delegated Tasks'},
  unassigned: {filter: {agents: ['null'], teams: ['null'], departments: ['null']}, label: 'Unassigned Tasks'},
  all: {filter: {done: 'all'}, label: 'All Tasks'}
};

export class Groups extends React.Component {

  static propTypes = {
    groups: PropTypes.object.isRequired
  };

  render() {
    const { groups } = this.props;

    return (
      <Section>
        <SectionHeader>Tasks</SectionHeader>
        <ul>
          {jQuery.map(groupOptions, (params, type) =>
            <ListItemContainer key={type}
                               urlHash={params.label}
                               listOptions={params.filter}>

              <ListItem count={groups.get(type)}
                        label={params.label} />
            </ListItemContainer>
          )}
        </ul>
      </Section>
    );
  }
}
