import React, { Component, PropTypes } from 'react';
import Immutable from 'immutable';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { Button } from './Button';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { DropdownPanel } from './DropdownPanel';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { AddLabelsContainer } from './AddLabelsContainer';
import { RemoveLabelsContainer } from './RemoveLabelsContainer';

import { connect } from 'react-redux';
@connect()
export class ActionContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    isActive: PropTypes.bool,
    setParams: PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func.isRequired,
    currentParams: PropTypes.object,
    id: PropTypes.number.isRequired,
    item: PropTypes.object.isRequired
  };

  componentWillMount() {
    this.setState({
      expanded: this.props.isActive
    });
  }

  toggleExpanded = (event) => {
    event.preventDefault();
    this.setState({ expanded: !this.state.expanded });
  };
  collapse = () => this.setState({ expanded: false });

  stateValue = (param) => {
    const {currentParams} = this.props;
    if (currentParams) {
      if (param instanceof Array) {
        const result = [];
        param.map(item => {
          let value = currentParams.get(item);
          if (Immutable.Iterable.isIterable(value)) {
            value = value.toJS();
            value.map(item1=> {
              result.push(item1);
            });
          }
        });
        return [...new Set(result)];
      }
      let value = currentParams.get(param);
      if (Immutable.Iterable.isIterable(value)) {
        value = value.toJS();
      }
      return value;
    }
  };

  renderFilterInfo = (labels) => {
    if (labels.length) {
      const result = [<span className="dpw-navigation-dropdown-item-inline-info">{labels[0]}</span>];
      if (labels.length > 1) {
        result.push(
          <span className="dpw-navigation-dropdown-item-inline-info dpw-navigation-dropdown-item-inline-info-extra">
            +{labels.length - 1}
          </span>
        );
      }

      return result;
    }

    return <span />;
  };

  unsetParams = (param) => {
    this.props.dispatch(this.props.resetSingleAction(param));
  };

  render() {
    const {id, item, setParams, currentParams, resetSingleAction } = this.props;

    const checkIfButtonHasValue = () => {
      if (!currentParams) {
        return false;
      }
      if (Boolean(currentParams.get(item.param)) === true) {
        return true;
      }
      if (item.options && item.options.length > 0) {
        let hasValue = false;
        item.options.forEach((option) => {
          if (option.nested && option.nested.length > 0) {
            option.nested.forEach(
              (nestedItem) => {
                if (currentParams.get(nestedItem.param) === nestedItem.value) {
                  hasValue = true;
                }
              }
            );
          }
        });
        return hasValue;
      }
      return false;
    };

    const choiceOtherAction = (option, key)=> {
      if (option.param === 'addLabels') {
        return (
          <AddLabelsContainer key={key}
                              option={option}
                              setParams={setParams}
                              stateValue={this.stateValue}
                              renderFilterInfo={this.renderFilterInfo}
                              unsetParams={this.unsetParams}/>
        );
      } else if (option.param === 'removeLabels') {
        return (
          <RemoveLabelsContainer key={key}
                                 option={option}
                                 setParams={setParams}
                                 stateValue={this.stateValue}
                                 renderFilterInfo={this.renderFilterInfo}
                                 unsetParams={this.unsetParams}/>
        );
      }
    };

    return (
      <li>
        <Button isActive={this.state.expanded}
                hasValue={!this.state.expanded && checkIfButtonHasValue()}
                ref={'button' + id}
                label={item.label}
                icon={item.icon}
                onClick={this.toggleExpanded}/>
        <Detached isOpen={this.state.expanded}
                  positionAt="left bottom"
                  positionTarget={this.refs['button' + id]}>
          <ClickOut onClickOut={this.collapse}
                    ignoreNodes={[this.refs.menuItem, '.dpw-navigation-dropdown-panel', '.dpw-label-list', '.dpw-item-label']}
                    additionalNodes={['.dpw-navigation-dropdown-item-clear']}>
            {item.type === 'action' &&
            <DropdownPanel item={item}
                           currentParams={currentParams}
                           setParams={setParams}
                           resetSingleAction={resetSingleAction}/>
            }
            {item.type === 'menu' &&
            <Menu>
              {item.options.map((option, key)=> choiceOtherAction(option, key))}
            </Menu>
            }
          </ClickOut>
        </Detached>
      </li>
    );
  }
}
