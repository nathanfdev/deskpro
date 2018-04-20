import React from 'react';
import PropTypes from 'prop-types';
import {
  Item,
  ListElementGroup,
  QueryableList,
  Scrollbar,
  Urgency,
  Count,
  Avatar,
} from '@deskpro/react-components';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { AvatarResolver } from 'DeskPRO/Component/Avatar';
import ItemFilter from './ItemFilter';

class TicketsForm extends React.Component {
  static propTypes = {
    onChange:           PropTypes.func,
    filter:             PropTypes.object,
    groupFields:        PropTypes.object,
    ticketCustomFields: PropTypes.object,
  };

  constructor(props) {
    super(props);
    this.state = {
      value: localStorage.getItem(`column_filter_subfilter_${props.filter.get('id')}`) || '@none'
    };
  }

  handleChange = (e) => {
    this.setState({ value: e.target.value }, () => {
      if (this.props.onChange) {
        localStorage.setItem(`column_filter_subfilter_${this.props.filter.get('id')}`, this.state.value);
        this.props.onChange(this.state.value);
      }
    });
  };

  eatClick = (e) => {
    e.stopPropagation();
  };

  render() {
    const formStyles = {
      formGroup: {
        padding: '3px 6px'
      },
      label: {
        display:       'block',
        fontSize:      '11px',
        textTransform: 'uppercase'
      },
      checkboxLabel: {
        display: 'block'
      }
    };

    const { value } = this.state;
    const {
      filter,
      groupFields,
      ticketCustomFields,
    } = this.props;

    const groups = {
      '@none': { title: 'None', className: 'none' },
    };

    groupFields.forEach((group) => {
      if (group.get('type') === 'ticket_field') {
        const f = ticketCustomFields.find(field => field.get('id') === group.get('field_id'));
        groups[group.get('id')] = {
          title:     f.get('title'),
          className: f.get('aliases').map(a => `option-${a}`).join(' ')
        };
      } else {
        groups[group.get('id')] = {
          title:     agentPhrases.get(`agent.grouping_option.${group.get('id')}`),
          className: ''
        };
      }
    });

    return (
      <div onClick={this.eatClick}>
        <Scrollbar autoHeightMax={205}>
          <div style={formStyles.formGroup}>
            <label style={formStyles.label}>
              Group by field
            </label>
            {Object.entries(groups).map(([key, group]) => (
              <label
                key={key}
                style={formStyles.checkboxLabel}
                htmlFor={`filter_${filter.get('id')}_group_${key}`}
                className={`filter-group-option filter-${filter.get('id')} option-${key} ${group.className}`}
              >
                <input
                  type="radio"
                  name="group"
                  id={`filter_${filter.get('id')}_group_${key}`}
                  value={key}
                  checked={value === key}
                  onChange={this.handleChange}
                /> {group.title}
              </label>
            ))}
          </div>
        </Scrollbar>
      </div>
    );
  }
}

class AgentAvatar extends React.Component {
  static propTypes = {
    defaultUrl: PropTypes.string,
    imageUrl:   PropTypes.string,
  };

  render() {
    let src = null;
    if (this.props.imageUrl) {
      src = this.props.imageUrl;
    } else if (this.props.defaultUrl) {
      src = this.props.defaultUrl;
    }
    if (src) {
      return <Avatar src={src} />;
    }
    return null;
  }
}

export default class Filter extends React.Component {
  static propTypes = {
    agents:             PropTypes.object,
    agentTeams:         PropTypes.object,
    filter:             PropTypes.object,
    filtersCounts:      PropTypes.object,
    groupFields:        PropTypes.object,
    ticketCustomFields: PropTypes.object,
    ticketDepartments:  PropTypes.object,
    mode:               PropTypes.object,
    onSelect:           PropTypes.func,
    onSelectMode:       PropTypes.func,
    onGroupingChange:   PropTypes.func,
  };

  constructor(props) {
    super(props);
    this.state = {
      ticketsWhereGroup: localStorage.getItem(`column_filter_subfilter_${props.filter.get('id')}`) || '@none',
    };
  }

  onSelectMode = (mode) => {
    const { filter, onSelectMode } = this.props;
    onSelectMode({
      type:          'filter',
      filter:        filter.get('id'),
      grouping:      mode.grouping,
      groupingValue: mode.groupingValue
    });
  };

  getSubFilters = () => {
    const { filter, mode } = this.props;
    const count = this.props.filtersCounts.find(e => e.get('id') === this.props.filter.get('id'));
    let list = null;
    if (!count) {
      return null;
    }
    switch (this.state.ticketsWhereGroup) {
      case '@none':
        list = null;
        break;
      case 'urgency':
        list = (<ListElementGroup name="urgency">
          <Item style={{ padding: '4px 12px 4px 6px' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
              {
                count.get('nested', []).map((group) => {
                  if (!group) {
                    return '';
                  }
                  const index = group.get('value');
                  return (
                    <Urgency
                      key={index}
                      level={index}
                      onClick={() => this.onSelectMode({ grouping: 'urgency', groupingValue: index })}
                    >
                      {group.get('count')}
                    </Urgency>
                  );
                })
              }
            </div>
          </Item>
        </ListElementGroup>);
        break;
      default: {
        const grouping = this.state.ticketsWhereGroup;
        list = (<ListElementGroup name={grouping}>
          {
            count.get('nested', []).map((group) => {
              if (!group) {
                return '';
              }
              const newMode = { grouping, groupingValue: group.get('id') || group.get('value') };
              let selected = false;
              if (
                mode &&
                mode.filter === filter.get('id') &&
                mode.grouping === grouping &&
                mode.groupingValue === (group.get('id') || group.get('value'))
              ) {
                selected = true;
              }
              switch (this.state.ticketsWhereGroup) {
                case 'agent': {
                  const agent = this.props.agents.find(a => a.get('id') === group.get('id'));
                  return (
                    <Item
                      key={group.get('id')}
                      selected={selected}
                      onClick={() => this.onSelectMode(newMode)}
                    >
                      <AvatarResolver avatar={agent.get('avatar')} size={10}>
                        <AgentAvatar />
                      </AvatarResolver>
                      {group.get('title')}
                      <Count>{group.get('count')}</Count>
                    </Item>
                  );
                }
                case 'department': {
                  const department = this.props.ticketDepartments.find(d => d.get('id') === group.get('id'));
                  return (
                    <Item
                      key={group.get('id')}
                      selected={selected}
                      onClick={() => this.onSelectMode(newMode)}
                    >
                      <AvatarResolver avatar={department.get('avatar')} size={10}>
                        <AgentAvatar />
                      </AvatarResolver>
                      {group.get('title')}
                      <Count>{group.get('count')}</Count>
                    </Item>
                  );
                }
                case 'agent_team': {
                  const team = this.props.agentTeams.find(t => t.get('id') === group.get('id'));
                  return (
                    <Item
                      key={group.get('id')}
                      selected={selected}
                      onClick={() => this.onSelectMode(newMode)}
                    >
                      <AvatarResolver avatar={team.get('avatar')} size={10}>
                        <AgentAvatar />
                      </AvatarResolver>
                      {group.get('title')}
                      <Count>{group.get('count')}</Count>
                    </Item>
                  );
                }
                default:
                  return (
                    <Item
                      key={group.get('id')}
                      selected={selected}
                      onClick={() => this.onSelectMode(newMode)}
                    >
                      {group.get('title')}
                      <Count>{group.get('count')}</Count>
                    </Item>
                  );
              }
            })
          }
        </ListElementGroup>);
        break;
      }
    }
    return (
      <li key="subfilter">
        <QueryableList whereName={this.state.ticketsWhereGroup}>
          {list}
        </QueryableList>
      </li>
    );
  };

  handleTicketsChange = (ticketsWhereGroup) => {
    this.setState({ ticketsWhereGroup });
    this.props.onGroupingChange(this.props.filter.get('id'), ticketsWhereGroup);
    if (this.filter) {
      this.filter.close();
    }
  };

  renderCount() {
    const count = this.props.filtersCounts.find(filter => filter.get('id') === this.props.filter.get('id'));
    if (count) {
      return <Count>{count.get('count')}</Count>;
    }
    return <Count>&middot;</Count>;
  }

  render() {
    const {
      filter,
      groupFields,
      ticketCustomFields,
      mode,
      onSelect,
    } = this.props;
    let selected = false;
    if (mode && mode.filter === filter.get('id') && !mode.grouping) {
      selected = true;
    }
    const render = [
      <Item
        key="item"
        selected={selected}
        onClick={onSelect}
      >
        {filter.get('title')}
        {this.renderCount()}
        <ItemFilter ref={(ref) => { this.filter = ref; }}>
          <TicketsForm
            onChange={this.handleTicketsChange}
            filter={filter}
            groupFields={groupFields}
            ticketCustomFields={ticketCustomFields}
          />
        </ItemFilter>
      </Item>
    ];
    const subFilters = this.getSubFilters();
    if (subFilters) {
      render.push(subFilters);
    }
    return render;
  }
}
