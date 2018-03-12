import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import {
  Item,
  ItemFilter,
  Checkbox,
  ListElementGroup,
  QueryableList,
  Scrollbar,
  Tag,
  Urgency,
  Count,
} from '@deskpro/react-components';
import { objects } from '@deskpro/react-components/dist/utils';

class Sla extends React.Component {
  static propTypes = {
    level:     PropTypes.oneOf(['passing', 'warning', 'failed']),
    className: '',
    children:  PropTypes.node,
  };
  render() {
    const {
            children, level, className, ...props
          } = this.props;
    return (
      <Tag
        className={classNames('dp-sla', level, className)}
        {...objects.objectKeyFilter(props, Sla.propTypes)}
      >
        {children}
      </Tag>
    );
  }
}

class TicketsForm extends React.Component {
  static propTypes = {
    onChange:    PropTypes.func,
    onSlaChange: PropTypes.func,
    filter:      PropTypes.object,
    slaValue:    PropTypes.bool,
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

    const groups = {
      '@none':      'None',
      urgency:      'Urgency',
      agent:        'Agent',
      'agent-team': 'Agent Team'
    };

    const { value } = this.state;
    const { filter } = this.props;

    return (
      <div>
        <div style={formStyles.formGroup}>
          <label
            htmlFor={`filter_${filter.get('id')}_sla`}
          >
            SLA View
          </label>
          <Checkbox
            id={`filter_${filter.get('id')}_sla`}
            onChange={this.props.onSlaChange}
            checked={this.props.slaValue}
          >
            Show SLAs
          </Checkbox>
        </div>
        <hr />
        <Scrollbar autoHeightMax={100} style={{ height: 110 }}>
          <div style={formStyles.formGroup}>
            <label style={formStyles.label}>
              Group by field
            </label>
            {Object.entries(groups).map(([key, group]) => (
              <label
                key={key}
                style={formStyles.checkboxLabel}
                htmlFor={`filter_${filter.get('id')}_group_${key}`}
              >
                <input
                  type="radio"
                  name="group"
                  id={`filter_${filter.get('id')}_group_${key}`}
                  value={key}
                  checked={value === key}
                  onChange={this.handleChange}
                /> {group}
              </label>
            ))}
          </div>
        </Scrollbar>
      </div>
    );
  }
}

export default class Filter extends React.Component {
  static propTypes = {
    filter: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      ticketsWhereGroup: localStorage.getItem(`column_filter_subfilter_${props.filter.get('id')}`) || '@none',
      slaValue:          false
    };
  }

  getSubFilters = () => (
    <li key="subfilter">
      <QueryableList whereName={this.state.ticketsWhereGroup}>
        <ListElementGroup name="agent">
          {
            this.props.filter.get('nested', []).map((group) => {
              if (!group) {
                return '';
              }
              const type = `agent-${group.get('id')}`;
              return (
                <Item
                  key={group.get('id')}
                  rightTypes={[Sla]}
                  onClick={() => this.onSelectMode({ type })}
                >
                  {group.get('title')}
                  { this.state.slaChecked ?
                  [
                    <Sla level="passing" onClick={() => this.onSelectMode({ type, sla: 'passing' })}>5</Sla>,
                    <Sla level="warning" onClick={() => this.onSelectMode({ type, sla: 'warning' })}>2</Sla>,
                    <Sla level="failed" onClick={() => this.onSelectMode({ type, sla: 'failed' })}>2</Sla>,
                  ]
                    : ''
                  }
                  <Count>{group.get('count')}</Count>
                </Item>
              );
            })
          }
        </ListElementGroup>
        <ListElementGroup name="urgency">
          <Item style={{ padding: '4px 12px 4px 6px' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
              {
                [...Array(10).keys()].map((key) => {
                  const index = key + 1;
                  return (
                    <Urgency key={index} level={index} onClick={() => this.onSelectMode({ type: `urgency${index}` })}>{Math.ceil(Math.random() * 30)}</Urgency>
                  );
                })
              }
            </div>
          </Item>
        </ListElementGroup>
        <ListElementGroup name="agent-team">
          <Item onClick={() => this.onSelectMode({ type: 'team', team: 1 })}>
              Support
            </Item>
          <Item onClick={() => this.onSelectMode({ type: 'team', team: 2 })}>
              Sales
            </Item>
        </ListElementGroup>
      </QueryableList>
    </li>
    );

  handleTicketsChange = (ticketsWhereGroup) => {
    this.setState({ ticketsWhereGroup });
    this.filter.close();
  };

  handleSlaChange = (slaValue) => {
    this.setState({
      slaValue
    });
  };

  render() {
    const { filter } = this.props;
    const render = [<Item key="item">
      {filter.get('title')}
      <Count>0</Count>
      {
        filter.get('filterable') ?
          <ItemFilter ref={(ref) => { this.filter = ref; }}>
            <TicketsForm
              onChange={this.handleTicketsChange}
              onSlaChange={this.handleSlaChange}
              filter={filter}
              slaValue={this.state.slaValue}
            />
          </ItemFilter>
          : ''
      }
    </Item>];
    const subFilters = this.getSubFilters();
    if (subFilters) {
      render.push(subFilters);
    }
    return render;
  }
}
