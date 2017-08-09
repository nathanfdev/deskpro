import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { Button } from 'deskpro-components/lib/Components/Buttons';
import { Label, Select, CustomSelect, Checkbox } from 'deskpro-components/lib/Components/Forms';
import { List, ListElement } from 'deskpro-components/lib/Components/Common';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { MassActionsSelect } from './Menus/MassActionsSelect';
import { OwnershipSelectContainer } from './Menus/OwnershipSelect';
import { allSnippetsSelector } from '../Selectors/snippets';

class DraftSelect extends React.PureComponent {
  static propTypes = {
    value:    PropTypes.object,
    setValue: PropTypes.func,
  };

  render() {
    const { value, setValue } = this.props;
    const options = [
      { value: 'draft', label: agentPhrases.get('agent.snippets.set_as_draft') },
      { value: 'published', label: agentPhrases.get('agent.snippets.set_as_published') },
    ];
    return (
      <Select
        value={value}
        options={options}
        clearable={false}
        searchable={false}
        onChange={setValue}
      />
    );
  }
}

@connect(state => ({
  agentTeams: allSelectorFactory('AgentTeam')(state)
}))
class OwnershipSelect extends React.Component {
  static propTypes = {
    agentTeams: PropTypes.object,
    value:      PropTypes.object,
    setValue:   PropTypes.func,
    snippets:   PropTypes.object,
    selected:   PropTypes.object,
  };

  constructor(props) {
    super(props);
    this.state = {
      selectedTeams:     new Set(),
      teamsExisting:     new Set(),
      isOwnershipGlobal: false,
    };

    this.initialExisting = new Set();
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.selected !== this.props.selected || !nextProps.snippets.equals(this.props.snippets)) {
      let isOwnershipGlobalChecked = false;
      let isOwnershipGlobalExisting = false;

      const teamsExisting = new Set();
      const selectedTeams = new Set();

      if (nextProps.selected.size) {
        const selectedSnippets = nextProps.snippets.filter(snippet => nextProps.selected.has(snippet.get('id')));

        isOwnershipGlobalExisting = selectedSnippets.some(snippet => snippet.get('is_ownership_global'));

        if (isOwnershipGlobalExisting) {
          isOwnershipGlobalChecked = selectedSnippets.count(snippet => !snippet.get('is_ownership_global')) === 0;
        }

        if (!isOwnershipGlobalChecked) {
          this.props.agentTeams.forEach((team) => {
            if (selectedSnippets.some(snippet =>
                snippet.get('ownership_teams', new Immutable.List()).includes(team.get('id')))
            ) {
              teamsExisting.add(team.get('id'));
              if (!isOwnershipGlobalExisting
                && selectedSnippets.count(snippet =>
                  !snippet.get('ownership_teams', new Immutable.List()).includes(team.get('id'))) === 0) {
                selectedTeams.add(team.get('id'));
              }
            }
          });
        }
      }

      this.initialExisting = new Set(teamsExisting);

      this.setState({
        isOwnershipGlobal: isOwnershipGlobalChecked,
        selectedTeams,
        teamsExisting,
      });
    }
  }

  handleChange = (teams, isOwnershipGlobal, remove) => {
    if (teams.size || remove) {
      const removedTeams = [...this.state.selectedTeams].filter(x => !teams.has(x));
      const newTeams = [...teams].filter(x => !this.state.selectedTeams.has(x));
      let { value } = this.props;
      if (!value) {
        value = { selectedTeams: {} };
      }
      newTeams.forEach((x) => { value.selectedTeams[x] = true; });
      const teamsExisting = new Set(this.state.teamsExisting);
      removedTeams.forEach((x) => {
        value.selectedTeams[x] = false;
        teamsExisting.delete(x);
      });
      this.setState({
        isOwnershipGlobal: false,
        selectedTeams:     teams,
        teamsExisting,
      });
      this.props.setValue({
        isOwnershipGlobal: true,
        selectedTeams:     value.selectedTeams,
      });
    } else {
      const toRemove = {};
      this.initialExisting.forEach((x) => { toRemove[x] = false; });
      this.props.setValue({
        isOwnershipGlobal,
        selectedTeams: toRemove,
      });
      this.setState({
        isOwnershipGlobal,
        selectedTeams: new Set(),
        teamsExisting: new Set(),
      });
    }
  };

  render() {
    return (
      <OwnershipSelectContainer
        selectedTeams={this.state.selectedTeams}
        isOwnershipGlobal={this.state.isOwnershipGlobal}
        existingTeams={this.state.teamsExisting}
        onChange={this.handleChange}
      />
    );
  }
}

class TypeSelect extends React.Component {
  static propTypes = {
    value:    PropTypes.object,
    setValue: PropTypes.func,
    snippets: PropTypes.object,
    selected: PropTypes.object,
  };

  constructor(props) {
    super(props);
    this.state = {
      values: {
        ticket: {
          checked:  false,
          existing: false,
        },
        chat: {
          checked:  false,
          existing: false,
        },
      },
    };
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.selected !== this.props.selected || !nextProps.snippets.equals(this.props.snippets)) {
      let ticketChecked = false;
      let ticketExisting = false;
      let chatChecked = false;
      let chatExisting = false;

      if (nextProps.selected.size) {
        const selectedSnippets = nextProps.snippets.filter(snippet => nextProps.selected.has(snippet.get('id')));

        ticketExisting = selectedSnippets.some(snippet => snippet.get('types').includes('ticket'));
        if (ticketExisting) {
          ticketChecked = selectedSnippets.count(snippet => !snippet.get('types').includes('ticket')) === 0;
        }
        chatExisting = selectedSnippets.some(snippet => snippet.get('types').includes('chat'));
        if (chatExisting) {
          chatChecked = selectedSnippets.count(snippet => !snippet.get('types').includes('chat')) === 0;
        }
      }

      this.setState({
        values: {
          ticket: {
            checked:  ticketChecked,
            existing: ticketExisting,
          },
          chat: {
            checked:  chatChecked,
            existing: chatExisting,
          },
        },
      });
    }
  }

  inputRenderer = () => agentPhrases.get('agent.general.select');

  handleChange = (checked, newValue) => {
    const { values } = this.state;
    values[newValue] = {
      checked,
      existing: false,
    };
    this.setState({
      values
    });
    let { value } = this.props;
    if (!value) {
      value = {};
    }
    value[newValue] = checked;
    this.props.setValue(value);
  };

  render() {
    const { values } = this.state;
    return (
      <CustomSelect
        inputRenderer={this.inputRenderer}
        displayInputWhenOpened={false}
      >
        <List>
          <ListElement>
            <Checkbox
              value="ticket"
              checked={values.ticket.checked}
              existing={values.ticket.existing}
              onChange={this.handleChange}
            >
              {agentPhrases.get('agent.general.ticket')}
            </Checkbox>
          </ListElement>
          <ListElement>
            <Checkbox
              value="chat"
              checked={values.chat.checked}
              existing={values.chat.existing}
              onChange={this.handleChange}
            >
              {agentPhrases.get('agent.general.chat')}
            </Checkbox>
          </ListElement>
          { this.props.selected.size
            && !values.ticket.checked && !values.ticket.existing
            && !values.chat.checked && !values.chat.existing ?
              <ListElement className="error">
                {agentPhrases.get('agent.snippets.at_least_one_type')}
              </ListElement>
            : null
          }
        </List>
      </CustomSelect>
    );
  }
}

@connect(state => ({
  snippets: allSnippetsSelector(state),
}))
export default class MassActions extends React.Component {
  static propTypes = {
    snippets: PropTypes.object,
    selected: PropTypes.object,
    action:   PropTypes.string.isRequired,
    close:    PropTypes.func,
  };

  constructor(props) {
    super(props);
    this.state = {
      actionValue: null
    };
    this.setActionValue = this.setActionValue.bind(this);
    this.getAction = this.getAction.bind(this);
    this.runAction = this.runAction.bind(this);
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.action !== this.props.action) {
      this.setState({
        actionValue: null
      });
    }
  }

  setActionValue = (value) => {
    this.setState({
      actionValue: value
    });
  };


  getAction() {
    switch (this.props.action) {
      case 'labels':
        return <span>Unknown action</span>;
      case 'visibility':
        return <span>Unknown action</span>;
      case 'ownership':
        return (<OwnershipSelect
          value={this.state.actionValue}
          setValue={this.setActionValue}
          selected={this.props.selected}
          snippets={this.props.snippets}
        />);
      case 'type':
        return (<TypeSelect
          value={this.state.actionValue}
          setValue={this.setActionValue}
          selected={this.props.selected}
          snippets={this.props.snippets}
        />);
      case 'export':
        return <span />;
      case 'draft':
        return (<DraftSelect
          value={this.state.actionValue}
          setValue={this.setActionValue}
        />);
      default:
        return <span>Unknown action</span>;
    }
  }

  runAction() {
    console.log(this.state.actionValue);
  }

  render() {
    const { action, close, selected } = this.props;
    const actions = MassActionsSelect.getActions();
    return (
      <div className="snippets__mass-actions--content">
        <a href="#close" className="close-cross" onClick={close}>
          {agentPhrases.get('agent.general.close_lc')}
        </a>
        <Label>
          {actions.find(a => a.value === action).text}
        </Label>
        {this.getAction()}
        <Button
          size="medium"
          onClick={this.runAction}
          disabled={selected.size === 0 || this.state.actionValue === null}
        >
          {this.props.action === 'export' ?
            agentPhrases.get('agent.general.export')
            : agentPhrases.get('agent.snippets.save_changes')
          }
        </Button>
        <span className="count">
          ({selected.size} {selected.size === 1 ?
            agentPhrases.get('agent.general.snippet')
          : agentPhrases.get('agent.general.snippets')})
        </span>
      </div>
    );
  }
}
