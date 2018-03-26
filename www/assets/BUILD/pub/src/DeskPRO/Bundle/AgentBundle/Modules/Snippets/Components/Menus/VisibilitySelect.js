import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import { CustomSelect, Radio, Checkbox, Input, List, ListElement } from '@deskpro/react-components';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

class Department extends React.Component {
  static propTypes = {
    department:          PropTypes.object.isRequired,
    departments:         PropTypes.object.isRequired,
    selectedDepartments: PropTypes.instanceOf(Set).isRequired,
    existingDepartments: PropTypes.instanceOf(Set),
    filter:              PropTypes.object,
    checked:             PropTypes.bool.isRequired,
    existing:            PropTypes.bool,
    onChange:            PropTypes.func.isRequired,
  };

  static defaultProps = {
    existing: false
  };

  getChildren(department) {
    const children = department.get('children');
    if (children.size) {
      const departments = [];
      children.forEach((child) => {
        const childDepartment = this.props.departments.find(d => d.get('id') === child);
        if (childDepartment) {
          departments.push(<Department
            key={childDepartment.get('id')}
            department={childDepartment}
            filter={this.props.filter}
            departments={this.props.departments}
            selectedDepartments={this.props.selectedDepartments}
            existingDepartments={this.props.existingDepartments}
            checked={this.props.selectedDepartments.has(childDepartment.get('id'))}
            existing={this.props.existingDepartments.has(childDepartment.get('id'))}
            onChange={this.props.onChange}
          />);
        }
      });
      return (
        <List>
          {departments}
        </List>
      );
    }
    return null;
  }

  render() {
    const { department, checked, existing, onChange, filter } = this.props;
    if (filter && !department.get('title').match(filter) && department.get('children').size === 0) {
      return null;
    } else if (filter && department.get('children').size > 0) {
      const children = department.get('children');
      if (children.count(child =>
        this.props.departments.find(d => d.get('id') === child).get('title').match(filter)) === 0) {
        return null;
      }
    }

    return (
      <ListElement>
        <Checkbox
          value={department.get('id')}
          onChange={(c, v) => onChange(c, v, department)}
          checked={checked}
          existing={existing}
        >
          {department.get('title')}
        </Checkbox>
        {this.getChildren(department)}
      </ListElement>
    );
  }
}

@connect(state => ({
  chatDepartments:   collectionSelectorFactory('Department', 'all_chat')(state),
  ticketDepartments: collectionSelectorFactory('Department', 'all_tickets')(state)
}))
export class VisibilitySelectContainer extends React.Component {
  static propTypes = {
    chatDepartments:     PropTypes.object,
    ticketDepartments:   PropTypes.object,
    selectedDepartments: PropTypes.instanceOf(Set).isRequired,
    existingDepartments: PropTypes.instanceOf(Set),
    isVisibleGlobal:     PropTypes.bool,
    types:               PropTypes.array,
    onChange:            PropTypes.func,
  };

  static defaultProps = {
    existingDepartments: new Set()
  };

  render() {
    const {
      chatDepartments,
      ticketDepartments,
      selectedDepartments,
      existingDepartments,
      onChange,
      types,
      isVisibleGlobal
    } = this.props;
    return (
      <VisibilitySelect
        chatDepartments={chatDepartments}
        ticketDepartments={ticketDepartments}
        selectedDepartments={selectedDepartments}
        existingDepartments={existingDepartments}
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
    selectedDepartments: PropTypes.instanceOf(Set),
    existingDepartments: PropTypes.instanceOf(Set),
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
      radio,
      filter: '',
    };
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.isVisibleGlobal !== this.props.isVisibleGlobal
      || nextProps.selectedDepartments !== this.props.selectedDepartments
      || nextProps.existingDepartments !== this.props.existingDepartments
    ) {
      let radio = '';
      if (nextProps.isVisibleGlobal) {
        radio = 'all';
      } else {
        radio = 'specific';
      }
      this.setState({
        radio,
      });
    }
  }

  onFilterChange = filter => this.setState({ filter });

  onRadioChange = (checked, value) => {
    this.setState({
      radio: value
    });
    if (value === 'all') {
      this.props.onChange(new Set(), true);
    } else {
      this.props.onChange(new Set(), false);
    }
    this.forceUpdate();
  };

  onCheckboxChange = (checked, value, department) => {
    const selectedDepartments = new Set(this.props.selectedDepartments);
    if (checked) {
      selectedDepartments.add(value);
      department.get('children').forEach(child => selectedDepartments.add(child));
      if (department.get('parent')) {
        let parent = this.props.ticketDepartments.get(department.get('parent'));
        if (!parent) {
          parent = this.props.chatDepartments.get(department.get('parent'));
        }
        if (parent.get('children').filter(child => !selectedDepartments.has(child)).size === 0) {
          selectedDepartments.add(parent.get('id'));
        }
      }
    } else {
      selectedDepartments.delete(value);
      department.get('children').forEach(child => selectedDepartments.delete(child));
      if (department.get('parent')) {
        selectedDepartments.delete(department.get('parent'));
      }
    }
    this.props.onChange(selectedDepartments, false);
    this.forceUpdate();
  };

  getSpecific = () => {
    const { selectedDepartments, existingDepartments, types } = this.props;
    const departments = [];
    let departmentsCount = 0;
    const re = new RegExp(this.state.filter, 'i');
    if (types.indexOf('ticket') !== -1) {
      departmentsCount += this.props.ticketDepartments.size;
      if (types.length > 1) {
        departments.push(
          <ListElement key="ticket_header">
            <FormattedMessage id="agent.general.ticket_departments" />
          </ListElement>
        );
      }
      this.props.ticketDepartments.forEach((department) => {
        if (!department.get('parent')) {
          departments.push(
            <Department
              key={department.get('id')}
              department={department}
              filter={re}
              departments={this.props.ticketDepartments}
              selectedDepartments={selectedDepartments}
              existingDepartments={existingDepartments}
              checked={selectedDepartments.has(department.get('id'))}
              existing={existingDepartments.has(department.get('id'))}
              onChange={this.onCheckboxChange}
            />
          );
        }
      });
    }
    if (types.indexOf('chat') !== -1) {
      departmentsCount += this.props.chatDepartments.size;
      if (types.length > 1) {
        departments.push(
          <ListElement key="chat_header">
            <FormattedMessage id="agent.general.chat_departments" />
          </ListElement>
        );
      }
      this.props.chatDepartments.forEach((department) => {
        if (!department.get('parent')) {
          departments.push(
            <Department
              key={department.get('id')}
              department={department}
              filter={re}
              departments={this.props.chatDepartments}
              selectedDepartments={selectedDepartments}
              checked={selectedDepartments.has(department.get('id'))}
              onChange={this.onCheckboxChange}
            />
          );
        }
      });
    }
    return (
      <div>
        {departmentsCount > 10 ?
          <FormattedMessage id="agent.general.filter">
            {placeholder => (
              <Input
                placeholder={placeholder}
                value={this.state.filter}
                className="departments_filter"
                onChange={this.onFilterChange}
              />
            )}
          </FormattedMessage>
          : null }
        <List>
          {departments}
        </List>
      </div>
    );
  };

  inputRenderer = () => {
    const { selectedDepartments, isVisibleGlobal, ticketDepartments, chatDepartments } = this.props;
    if (isVisibleGlobal) {
      return <FormattedMessage id="agent.snippets.all_departments" />;
    } else if (selectedDepartments.size === 0) {
      return <FormattedMessage id="agent.snippets.please_select" />;
    } else if (selectedDepartments.size <= 3) {
      let departments = ticketDepartments.filter(t => selectedDepartments.has(t.get('id')))
        .map(o => o.get('title')).toArray();
      departments = departments.concat(chatDepartments.filter(t => selectedDepartments.has(t.get('id')))
        .map(o => o.get('title')).toArray());
      return departments.join(', ');
    }
    return (<FormattedMessage id="agent.general.departments">
      {txt => `${selectedDepartments.size} ${txt.toLowerCase()}`}
    </FormattedMessage>);
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
              <FormattedMessage id="agent.snippets.all_departments" />
            </Radio>
          </ListElement>
          <ListElement>
            <Radio
              name="visibility"
              value="specific"
              onChange={this.onRadioChange}
              checked={radio === 'specific'}
            >
              <FormattedMessage id="agent.snippets.specific_departments" />
            </Radio>
          </ListElement>
          { this.state.radio === 'specific' ? this.getSpecific() : null}
        </List>
      </CustomSelect>
    );
  }
}
