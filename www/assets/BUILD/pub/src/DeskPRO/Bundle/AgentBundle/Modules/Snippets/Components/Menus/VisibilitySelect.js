import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { CustomSelect, Radio, Checkbox } from 'deskpro-components/lib/Components/Forms';
import { List, ListElement } from 'deskpro-components/lib/Components/Common';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

@connect(state => ({
  chatDepartments:   collectionSelectorFactory('Department', 'all_chat')(state),
  ticketDepartments: collectionSelectorFactory('Department', 'all_tickets')(state)
}))
export class VisibilitySelectContainer extends React.Component {
  static propTypes = {
    chatDepartments:     PropTypes.object,
    ticketDepartments:   PropTypes.object,
    selectedDepartments: PropTypes.object.isRequired,
    isVisibleGlobal:     PropTypes.bool,
    types:               PropTypes.array,
    onChange:            PropTypes.func,
  };

  render() {
    const {
      chatDepartments,
      ticketDepartments,
      selectedDepartments,
      onChange,
      types,
      isVisibleGlobal
    } = this.props;
    return (
      <VisibilitySelect
        chatDepartments={chatDepartments}
        ticketDepartments={ticketDepartments}
        selectedDepartments={selectedDepartments}
        isVisibleGlobal={isVisibleGlobal}
        types={types}
        onChange={onChange}
      />
    );
  }
}
export class VisibilitySelect extends React.Component {
  static propTypes = {
    chatDepartments:     PropTypes.object,
    ticketDepartments:   PropTypes.object,
    selectedDepartments: PropTypes.object,
    isVisibleGlobal:     PropTypes.bool,
    types:               PropTypes.array,
    onChange:            PropTypes.func,
  };

  constructor(props) {
    super(props);
    let radio = '';
    if (this.props.isVisibleGlobal) {
      radio = 'all';
    } else {
      radio = 'specific';
    }
    this.state = {
      radio
    };
  }

  onRadioChange = (checked, value) => {
    this.setState({
      radio: value
    });
    if (value === 'all') {
      this.props.onChange(new Set(), true);
    } else {
      this.props.onChange(new Set(), false);
    }
  };

  onCheckboxChange = (checked, value, department) => {
    console.log(department);
    const { selectedDepartments } = this.props;
    if (checked) {
      selectedDepartments.add(value);
    } else {
      selectedDepartments.delete(value);
    }
    this.props.onChange(selectedDepartments, false);
    this.forceUpdate();
  };

  getSpecific = () => {
    const { selectedDepartments, types } = this.props;
    const departments = [];
    if (types.indexOf('ticket') !== -1) {
      if (types.length > 1) {
        departments.push(
          <ListElement key="ticket_header">
            {agentPhrases.get('agent.general.ticket_departments')}
          </ListElement>
        );
      }
      this.props.ticketDepartments.forEach((department) => {
        departments.push(
          <ListElement key={department.get('id')}>
            <Checkbox
              value={department.get('id')}
              onChange={(checked, value) => this.onCheckboxChange(checked, value, department)}
              checked={selectedDepartments.has(department.get('id'))}
            >
              {department.get('title')}
            </Checkbox>
          </ListElement>
        );
      });
    }
    if (types.indexOf('chat') !== -1) {
      if (types.length > 1) {
        departments.push(
          <ListElement key="chat_header">
            {agentPhrases.get('agent.general.chat_departments')}
          </ListElement>
        );
      }
      this.props.chatDepartments.forEach((department) => {
        departments.push(
          <ListElement key={department.get('id')}>
            <Checkbox
              value={department.get('id')}
              onChange={this.onCheckboxChange}
              checked={selectedDepartments.has(department.get('id'))}
            >
              {department.get('title')}
            </Checkbox>
          </ListElement>
        );
      });
    }
    return (
      <List>
        {departments}
      </List>
    );
  };

  inputRenderer = () => {
    const { selectedDepartments, isVisibleGlobal, ticketDepartments, chatDepartments } = this.props;
    if (isVisibleGlobal) {
      return agentPhrases.get('agent.snippets.all_departments');
    } else if (selectedDepartments.size === 0) {
      return agentPhrases.get('agent.snippets.please_select');
    } else if (selectedDepartments.size <= 3) {
      let departments = ticketDepartments.filter(t => selectedDepartments.has(t.get('id')))
        .map(o => o.get('title')).toArray();
      departments = departments.concat(chatDepartments.filter(t => selectedDepartments.has(t.get('id')))
        .map(o => o.get('title')).toArray());
      return departments.join(', ');
    }
    return `${selectedDepartments.size} ${agentPhrases.get('agent.general.departments').toLowerCase()}`;
  };

  render() {
    const { radio } = this.state;
    return (
      <CustomSelect
        inputRenderer={this.inputRenderer}
        displayInputWhenOpened={false}
      >
        <List>
          <ListElement>
            <Radio
              name="visibility"
              value="all"
              onChange={this.onRadioChange}
              checked={radio === 'all'}
            >
              {agentPhrases.get('agent.snippets.all_departments')}
            </Radio>
          </ListElement>
          <ListElement>
            <Radio
              name="visibility"
              value="specific"
              onChange={this.onRadioChange}
              checked={radio === 'specific'}
            >
              {agentPhrases.get('agent.snippets.specific_departments')}
            </Radio>
          </ListElement>
          { this.state.radio === 'specific' ? this.getSpecific() : null}
        </List>
      </CustomSelect>
    );
  }
}
