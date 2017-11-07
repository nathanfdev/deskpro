import PropTypes from 'prop-types';
import React, { Component } from 'react';
import Immutable from 'immutable';
import { Button } from '../Button';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { FilteringMenu } from './FilteringMenu';

export class FilteringMenuContainer extends Component {
  static propTypes = {
    onMenuUnmount: PropTypes.func,
    filters:       PropTypes.array.isRequired,
    unsetParam:    PropTypes.func.isRequired,
    setParam:      PropTypes.func.isRequired,
    currentParams: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = { expanded: false };
  }

  getButtonLabel() {
    const { currentParams, filters = [] } = this.props;
    let label = '(none)';
    let count = 0;
    let value;
    filters.map(filter => {
      if (filter.hasOwnProperty('param')) {
        const params = [filter.param];
        if (filter.hasOwnProperty('options')) {
          filter.options.map(option => {
            if (option.hasOwnProperty('nested')) {
              option.nested.map(opt => {
                params.push(opt.param);
              });
            }
          });
        }
        value = this.stateValue(params);
      } else if (filter.hasOwnProperty('fromParam')) {
        value = currentParams[filter.fromParam];
        if (!value) {
          value = currentParams[filter.toParam];
        }
      }
      if (value && ((value instanceof Array && value.length) || !(value instanceof Array))) {
        label = filter.label;
        count++;
      }
    });

    if (count > 1) {
      label = count + ' Options';
    }

    return label;
  }

  stateValue(param) {
    const { currentParams } = this.props;
    if (param instanceof Array) {
      const result = [];
      param.map(item => {
        let value = currentParams[item];
        if (Immutable.Iterable.isIterable(value)) {
          value = value.toJS();
          if (value.isArray) {
            value.map(item1 => {
              result.push(item1);
            });
          } else {
            for (var property in value) {
              if (value.hasOwnProperty(property)) {
                result.push(value[property]);
              }
            }
          }
        } else if (value) {
          result.push(value);
        }
      });
      return [...new Set(result)];
    }
    let value = currentParams[param];
    if (Immutable.Iterable.isIterable(value)) {
      value = value.toJS();
    }
    return value;
  }

  toggleExpanded = () => this.setState({ expanded: !this.state.expanded });
  collapse = () => this.setState({ expanded: false });

  render() {
    const { currentParams, unsetParam, setParam, onMenuUnmount, filters = [] } = this.props;

    return (
      <li ref="menuItem">
        <Button isActive={this.state.expanded}
          ref="button"
          title="Filter by:"
          icon={null}
          label={this.getButtonLabel()}
          onClick={this.toggleExpanded}
        />
        <Detached isOpen={this.state.expanded}
          positionAt="left bottom"
          positionTarget={this.refs.button}
        >
          <ClickOut onClickOut={this.collapse}
            ignoreNodes={[this.refs.menuItem, '.dpw-navigation-dropdown-panel', '.dpw-label-list', '.dpw-item-label', '.anytime-picker']}
            additionalNodes={['.dpw-navigation-dropdown-item-clear']}
          >
            <FilteringMenu filters={filters}
              currentParams={currentParams}
              unsetParam={unsetParam}
              onMenuUnmount={onMenuUnmount}
              setParam={setParam}
            />
          </ClickOut>
        </Detached>
      </li>
    );
  }

}
