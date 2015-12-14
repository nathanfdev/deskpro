import React, { Component, PropTypes } from 'react';
import Immutable from 'immutable';
import { connect } from 'react-redux';
import { Button } from '../Button';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { FilteringMenu } from './FilteringMenu';


@connect()
export class FilteringMenuContainer extends Component {
  static propTypes = {
    onMenuUnmount: PropTypes.func,
    dispatch: PropTypes.func.isRequired,
    filters: PropTypes.array.isRequired,
    setParamsAction: PropTypes.func.isRequired,
    state: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = { expanded: false };
  }

  toggleExpanded = () => this.setState({ expanded: !this.state.expanded });
  collapse = () => this.setState({ expanded: false });

  stateValue(param) {
    if (param instanceof Array) {
      const result = [];
      param.map(item => {
        let value = this.props.state.get(item);
        if (Immutable.Iterable.isIterable(value)) {
          value = value.toJS();
          value.map(item1=> {
            result.push(item1);
          });
        }
      });
      return [...new Set(result)];
    }
    let value = this.props.state.get(param);
    if (Immutable.Iterable.isIterable(value)) {
      value = value.toJS();
    }
    return value;
  }

  getButtonLabel() {
    const { filters = [] } = this.props;
    let label = '(none)';
    let count = 0;
    let value;
    filters.map(filter => {
      if (filter.hasOwnProperty('param')) {
        const params = [filter.param];
        if (filter.hasOwnProperty('options')) {
          filter.options.map(option=> {
            if (option.hasOwnProperty('nested')) {
              option.nested.map(opt => {
                params.push(opt.param);
              });
            }
          });
        }
        value = this.stateValue(params);
      } else if (filter.hasOwnProperty('fromParam')) {
        value = this.stateValue(filter.fromParam);
        if (!value) {
          value = this.stateValue(filter.toParam);
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

  render() {
    const { dispatch, state, setParamsAction, onMenuUnmount, filters = [] } = this.props;

    return (
      <li ref="menuItem">
        <Button
          isActive={this.state.expanded}
          ref="button"
          title="Filter by:"
          icon={null}
          label={this.getButtonLabel()}
          onClick={this.toggleExpanded}
          />
        <Detached isOpen={this.state.expanded}
                    positionAt="left bottom"
                    positionTarget={this.refs.button}>
          <ClickOut onClickOut={this.collapse}
                    ignoreNodes={[this.refs.menuItem, '.dpw-navigation-dropdown-panel', '.dpw-label-list']}
                    additionalNodes={['.dpw-navigation-dropdown-item-clear']}>
            <FilteringMenu dispatch={dispatch}
                           filters={filters}
                           state={state}
                           stateValue={this.stateValue.bind(this)}
                           onMenuUnmount={onMenuUnmount}
                           setParamsAction={setParamsAction}/>
          </ClickOut>
        </Detached>
      </li>
    );
  }
}
