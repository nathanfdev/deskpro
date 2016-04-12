import React, { PropTypes } from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ListItemContainer } from '../ListItemContainer';
import jQuery from 'jquery';

export class Groups extends React.Component {

  static propTypes = {
    options: PropTypes.object.isRequired,
    count:   PropTypes.object.isRequired
  };

  render() {
    const { options, count } = this.props;

    return (
      <Section>
        <SectionHeader>Tasks</SectionHeader>
        <ul>
          {jQuery.map(options, (params, type) =>
            <ListItemContainer key={type}
                               urlHash={params.label}
                               listOptions={params.filter}>

              <ListItem count={count.get(type)}
                        label={params.label} />
            </ListItemContainer>
          )}
        </ul>
      </Section>
    );
  }
}
