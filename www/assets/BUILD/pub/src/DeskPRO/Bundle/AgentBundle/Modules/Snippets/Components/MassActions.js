import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Button } from 'deskpro-components/lib/Components/Buttons';
import { Label, Select, CustomSelect, Checkbox } from 'deskpro-components/lib/Components/Forms';
import { List, ListElement } from 'deskpro-components/lib/Components/Common';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { MassActionsSelect } from './Menus/MassActionsSelect';
import { allSnippetsSelector } from '../Selectors/snippets';

class DraftSelect extends React.PureComponent {
  static propTypes = {
    value:    PropTypes.object,
    setValue: PropTypes.func,
  };

  render() {
    const { value, setValue } = this.props;
    const options = [
      { value: true, label: agentPhrases.get('agent.snippets.set_as_draft') },
      { value: true, label: agentPhrases.get('agent.snippets.set_as_published') },
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
      const ticketChecked = nextProps.selected.size
        && nextProps.snippets.filter(snippet => nextProps.selected.has(snippet.get('id')))
        .count(snippet => !snippet.get('types').includes('ticket')) === 0;
      const ticketExisting = nextProps.selected.size
        && nextProps.snippets.filter(snippet => nextProps.selected.has(snippet.get('id')))
        .some(snippet => snippet.get('types').includes('ticket'));
      const chatChecked = nextProps.selected.size
        && nextProps.snippets.filter(snippet => nextProps.selected.has(snippet.get('id')))
        .count(snippet => !snippet.get('types').includes('chat')) === 0;
      const chatExisting = nextProps.selected.size
        && nextProps.snippets.filter(snippet => nextProps.selected.has(snippet.get('id')))
        .some(snippet => snippet.get('types').includes('chat'));

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
        return <span>Unknown action</span>;
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
          disabled={selected.size === 0}
        >
          {this.props.action === 'export' ?
            agentPhrases.get('agent.general.export')
            : agentPhrases.get('agent.snippets.save_changes')
          }
        </Button>
      </div>
    );
  }
}
