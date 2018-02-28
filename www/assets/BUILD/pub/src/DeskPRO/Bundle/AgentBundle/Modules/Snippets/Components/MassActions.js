import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { Button, List, ListElement, Label, Select, CustomSelect, Checkbox, Input } from '@deskpro/react-components';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { allSelectorFactory, collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { MassActionsSelect } from './Menus/MassActionsSelect';
import { VisibilitySelectContainer } from './Menus/VisibilitySelect';
import { OwnershipSelectContainer } from './Menus/OwnershipSelect';
import { allSnippetsSelector, allSnippetLabelsSelector } from '../Selectors/snippets';

@connect(state => ({
  snippetLabels: allSnippetLabelsSelector(state)
}))
class LabelSelect extends React.Component {
  static propTypes = {
    snippetLabels: PropTypes.object,
    value:         PropTypes.object,
    setValue:      PropTypes.func,
    snippets:      PropTypes.object,
    selected:      PropTypes.object,
  };

  constructor(props) {
    super(props);
    const labels = this.parseLabels(props.snippetLabels);
    this.state = {
      filter:         '',
      labels,
      selectedLabels: new Set(),
      labelsExisting: new Set(),
    };
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.snippetLabels !== this.props.snippetLabels) {
      const labels = this.parseLabels(nextProps.snippetLabels);
      this.setState({
        labels
      });
    }
    if (nextProps.selected !== this.props.selected || !nextProps.snippets.equals(this.props.snippets)) {
      const labelExisting = new Set();
      const selectedLabels = new Set();

      if (nextProps.selected.size) {
        const selectedSnippets = nextProps.snippets.filter(snippet => nextProps.selected.has(snippet.get('id')));

        this.props.snippetLabels.forEach((label) => {
          if (selectedSnippets.some(snippet =>
              snippet.get('labels', new Immutable.List()).includes(label.get('label')))
          ) {
            labelExisting.add(label.get('id'));
            if (selectedSnippets.count(snippet =>
                !snippet.get('labels', new Immutable.List()).includes(label.get('label'))) === 0) {
              selectedLabels.add(label.get('id'));
            }
          }
        });
      }

      this.setState({
        selectedLabels,
        labelsExisting: labelExisting,
      });
    }
  }

  onFilterChange = filter => this.setState({ filter });

  getChildren = (label) => {
    const labels = label.get('children');
    if (!labels.size) {
      return null;
    }
    return <List>{this.getLabels(labels)}</List>;
  };

  getLabels = (labels) => {
    const result = [];

    function findInChildren(value, re) {
      if (value.get('children').find(e => e.get('tag').match(re))) {
        return true;
      }
      let found = false;
      value.get('children').forEach((child) => {
        if (child.get('children').size) {
          found = findInChildren(child, re);
          if (found) {
            return false;
          }
        }
        return true;
      });
      return found;
    }

    labels
      .filter((value) => {
        const { filter } = this.state;
        if (filter === '') {
          return true;
        }
        const re = new RegExp(filter, 'i');
        return value.get('tag').match(re) || findInChildren(value, re);
      })
      .sort((a, b) => {
        const atag = a.get('tag').toLowerCase();
        const btag = b.get('tag').toLowerCase();
        if (atag > btag) {
          return 1;
        } else if (atag < btag) {
          return -1;
        }
        return 0;
      })
      .forEach((label, key) => {
        result.push(
          <ListElement
            key={key}
          >
            <div className="element">
              <Checkbox
                value={label.get('tag')}
                checked={this.state.selectedLabels.has(label.get('tag'))}
                existing={this.state.labelsExisting.has(label.get('tag'))}
                onChange={this.handleChange}
                stopPropagation
              >
                <span className="tag">
                  {label.get('label')}
                </span>
              </Checkbox>
            </div>
            {this.getChildren(label)}
          </ListElement>
        );
      });
    if (result.length === 0 && this.state.filter !== '') {
      result.push(
        <ListElement
          key="create_label"
        >
          {agentPhrases.get('agent.snippets.no_results_new_label', { label: this.state.filter })}
        </ListElement>
      );
    }
    return result;
  };

  parseLabels(labels) {
    const hierarchy = {};
    labels.forEach((label) => {
      const parts = label.get('label').split('/');
      this.insertInHierarchy(parts[0].trim(), parts[0].trim(), hierarchy, parts.slice(1));
    });
    return Immutable.fromJS(hierarchy);
  }

  addLabel(label) {
    const labels = this.state.labels.toJS();
    const selectedLabels = new Set(this.state.selectedLabels);
    const parts = label.split('/');
    this.insertInHierarchy(parts[0].trim(), parts[0].trim(), labels, parts.slice(1));
    selectedLabels.add(label.replace(/\s*\/\s*/, '/'));
    this.setState({
      labels: Immutable.fromJS(labels),
      selectedLabels,
      filter: '',
    });
  }

  insertInHierarchy(tag, label, hierarchy, parts) {
    if (!hierarchy[label]) {
      hierarchy[label] = {
        label,
        tag,
        children: {}
      };
    }
    if (parts.length > 0) {
      this.insertInHierarchy(
        `${tag}/${parts[0].trim()}`,
        parts[0].trim(),
        hierarchy[label].children,
        parts.slice(1)
      );
    }
  }

  inputRenderer = () => agentPhrases.get('agent.general.select');

  handleChange = (checked, newValue) => {
    const labelsExisting = new Set(this.state.labelsExisting);
    const selectedLabels = new Set(this.state.selectedLabels);
    if (checked) {
      selectedLabels.add(newValue);
    } else {
      selectedLabels.delete(newValue);
      labelsExisting.delete(newValue);
    }
    this.setState({
      labelsExisting,
      selectedLabels,
    });
    let { value } = this.props;
    if (!value) {
      value = {};
    }
    value[newValue] = checked;
    this.props.setValue(value);
  };

  /**
   * Handles pressing ENTER in the search box
   */
  handleInputKeyDown = (e) => {
    if (e.keyCode === 13) {
      this.addLabel(e.target.value);
    }
  };

  render() {
    return (
      <CustomSelect
        inputRenderer={this.inputRenderer}
        displayInputWhenOpened={false}
      >
        <Input
          placeholder={agentPhrases.get('agent.general.filter')}
          value={this.state.filter}
          className="labels_filter"
          onChange={this.onFilterChange}
          onKeyDown={this.handleInputKeyDown}
        />
        <List>
          {this.getLabels(this.state.labels)}
        </List>
      </CustomSelect>
    );
  }
}

@connect(state => ({
  chatDepartments:   collectionSelectorFactory('Department', 'all_chat')(state),
  ticketDepartments: collectionSelectorFactory('Department', 'all_tickets')(state)
}))
class VisibilitySelect extends React.Component {
  static propTypes = {
    chatDepartments:   PropTypes.object,
    ticketDepartments: PropTypes.object,
    value:             PropTypes.object,
    setValue:          PropTypes.func,
    snippets:          PropTypes.object,
    selected:          PropTypes.object,
  };

  constructor(props) {
    super(props);
    this.state = {
      selectedDepartments: new Set(),
      departmentsExisting: new Set(),
      isVisibleGlobal:     false,
    };

    this.initialExisting = new Set();
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.selected !== this.props.selected || !nextProps.snippets.equals(this.props.snippets)) {
      let isVisibleGlobalChecked = false;
      let isVisibleGlobalExisting = false;

      const departmentsExisting = new Set();
      const selectedTeams = new Set();

      if (nextProps.selected.size) {
        const selectedSnippets = nextProps.snippets.filter(snippet => nextProps.selected.has(snippet.get('id')));

        isVisibleGlobalExisting = selectedSnippets.some(snippet => snippet.get('is_visible_global'));

        if (isVisibleGlobalExisting) {
          isVisibleGlobalChecked = selectedSnippets.count(snippet => !snippet.get('is_visible_global')) === 0;
        }

        if (!isVisibleGlobalChecked) {
          this.props.ticketDepartments.forEach((department) => {
            if (selectedSnippets.some(snippet =>
                snippet.get('visible_departments', new Immutable.List()).includes(department.get('id')))
            ) {
              departmentsExisting.add(department.get('id'));
              if (!isVisibleGlobalExisting
                && selectedSnippets.count(snippet =>
                  !snippet.get('visible_departments', new Immutable.List()).includes(department.get('id'))) === 0) {
                selectedTeams.add(department.get('id'));
              }
            }
          });
          this.props.chatDepartments.forEach((department) => {
            if (selectedSnippets.some(snippet =>
                snippet.get('visible_departments', new Immutable.List()).includes(department.get('id')))
            ) {
              departmentsExisting.add(department.get('id'));
              if (!isVisibleGlobalExisting
                && selectedSnippets.count(snippet =>
                  !snippet.get('visible_departments', new Immutable.List()).includes(department.get('id'))) === 0) {
                selectedTeams.add(department.get('id'));
              }
            }
          });
        }
      }

      this.initialExisting = new Set(departmentsExisting);

      this.setState({
        isVisibleGlobal: isVisibleGlobalChecked,
        selectedTeams,
        departmentsExisting,
      });
    }
  }

  handleChange = (departments, isVisibleGlobal, remove) => {
    if (departments.size || remove) {
      const removedDepartments = [...this.state.selectedDepartments].filter(x => !departments.has(x));
      const newDepartments = [...departments].filter(x => !this.state.selectedDepartments.has(x));
      let { value } = this.props;
      if (!value) {
        value = { selectedDepartments: {} };
      }
      newDepartments.forEach((x) => { value.selectedDepartments[x] = true; });
      const departmentsExisting = new Set(this.state.departmentsExisting);
      removedDepartments.forEach((x) => {
        value.selectedDepartments[x] = false;
        departmentsExisting.delete(x);
      });
      this.setState({
        isVisibleGlobal:     false,
        selectedDepartments: departments,
        departmentsExisting,
      });
      this.props.setValue({
        isVisibleGlobal:     false,
        selectedDepartments: value.selectedDepartments,
      });
    } else {
      const toRemove = {};
      this.initialExisting.forEach((x) => { toRemove[x] = false; });
      this.props.setValue({
        isVisibleGlobal,
        selectedDepartments: toRemove,
      });
      this.setState({
        isVisibleGlobal,
        selectedDepartments: new Set(),
        departmentsExisting: new Set(),
      });
    }
  };

  render() {
    return (
      <VisibilitySelectContainer
        selectedDepartments={this.state.selectedDepartments}
        isVisibleGlobal={this.state.isVisibleGlobal}
        existingDepartments={this.state.departmentsExisting}
        onChange={this.handleChange}
        types={['ticket', 'chat']}
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
        isOwnershipGlobal: false,
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

class DraftSelect extends React.PureComponent {
  static propTypes = {
    value:    PropTypes.string,
    setValue: PropTypes.func,
  };

  handleChange = (value) => {
    this.props.setValue(value.value);
  };

  render() {
    const { value } = this.props;
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
        onChange={this.handleChange}
      />
    );
  }
}

@connect(state => ({
  snippets: allSnippetsSelector(state),
}))
export default class MassActions extends React.Component {
  static propTypes = {
    snippets:       PropTypes.object,
    selected:       PropTypes.object,
    action:         PropTypes.string.isRequired,
    close:          PropTypes.func,
    runMassActions: PropTypes.func,
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
        return (<LabelSelect
          value={this.state.actionValue}
          setValue={this.setActionValue}
          selected={this.props.selected}
          snippets={this.props.snippets}
        />);
      case 'visibility':
        return (<VisibilitySelect
          value={this.state.actionValue}
          setValue={this.setActionValue}
          selected={this.props.selected}
          snippets={this.props.snippets}
        />);
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
    const payload = {
      action: this.props.action,
      value:  this.state.actionValue
    };
    this.props.runMassActions(payload);
    this.props.close();
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
          disabled={selected.size === 0 || (this.state.actionValue === null && this.props.action !== 'export')}
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
