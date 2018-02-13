import PropTypes from 'prop-types';
import React from 'react';
import { Icon, CustomSelect, List, ListElement } from '@deskpro/react-components';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';

export class MassActionsSelect extends React.PureComponent {
  static propTypes = {
    onChange: PropTypes.func,
  };

  static getActions() {
    return [
      {
        value: 'labels',
        icon:  'tag',
        text:  agentPhrases.get('agent.general.labels'),
      },
      {
        value: 'visibility',
        icon:  'eye',
        text:  agentPhrases.get('agent.snippets.visibility'),
      },
      {
        value: 'ownership',
        icon:  'users',
        text:  agentPhrases.get('agent.snippets.ownership'),
      },
      {
        value: 'type',
        icon:  'envelope-o',
        text:  agentPhrases.get('agent.general.type'),
      },
      {
        value: 'export',
        icon:  'download',
        text:  agentPhrases.get('agent.general.export'),
      },
      {
        value: 'draft',
        icon:  'pencil',
        text:  agentPhrases.get('agent.snippets.draft_status'),
      },
    ];
  }

  onChange = (langId) => {
    this.props.onChange(langId);
    this.select.toggleOpened();
  };

  inputRenderer = () => <span>{agentPhrases.get('agent.general.mass_actions')}</span>;

  render() {
    return (
      <CustomSelect
        inputRenderer={this.inputRenderer}
        displayInputWhenOpened={false}
        className="mass-actions"
        ref={(c) => { this.select = c; }}
      >
        <List
          className="dp-selectable-list"
        >
          {MassActionsSelect.getActions().map(action =>
            <ListElement
              key={action.value}
              onClick={() => this.onChange(action.value)}
            >
              <Icon name={action.icon} /> {action.text}
            </ListElement>
          )}
        </List>
      </CustomSelect>
    );
  }
}
