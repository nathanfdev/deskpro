import React, { Component, PropTypes } from 'react';
import Immutable from 'immutable';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { Button } from './Button';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { SingleChoicePanel } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/SingleChoicePanel';
import { ChoiceMenu, ChoiceMenuOption } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { AddLabelsContainer } from './AddLabelsContainer';
import { RemoveLabelsContainer } from './RemoveLabelsContainer';

import { connect } from 'react-redux';
@connect()
export class ActionContainer extends Component {
  static propTypes = {
    isActive: PropTypes.bool,
    dispatch: PropTypes.func.isRequired,
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

  checkBoxClick = (value) => {
    const {dispatch, setParams, currentParams} = this.props;
    // console.log('Other', currentParams.get('other').toArray());
    const other = currentParams.get('other') ? currentParams.get('other').toArray() : [];
    const valueIndex = other.indexOf(value);
    if (valueIndex > -1) {
      other.splice(valueIndex, 1);
    } else {
      other.push(value);
    }
    dispatch(setParams({ 'other': other }));
  };

  choiceOtherAction = (option, key)=> {
    const {setParams, currentParams, resetSingleAction } = this.props;

    if (option.param === 'add_labels') {
      return (
        <AddLabelsContainer key={key}
                            option={option}
                            setParams={setParams}
                            currentParams={currentParams}
                            stateValue={this.stateValue}
                            unsetParams={resetSingleAction}/>
      );
    } else if (option.param === 'remove_labels') {
      return (
        <RemoveLabelsContainer key={key}
                               option={option}
                               setParams={setParams}
                               currentParams={currentParams}
                               stateValue={this.stateValue}
                               unsetParams={resetSingleAction}/>
      );
    }
  };

  renderPanel = (item) => {
    const {setParams, currentParams, resetSingleAction } = this.props;
    if (item.type === 'set_action') {
      return (
        <SingleChoicePanel item={item}
                           currentParams={currentParams}
                           setParams={setParams}
                           resetSingleAction={resetSingleAction}/>
      );
    } else if (item.type === 'select_action') {
      const values = currentParams.get('other') ? currentParams.get('other').toArray() : [];
      return (
        <ChoiceMenu>
          <ul>
            {item.options.map((option, index) => <ChoiceMenuOption key={index}
                                                                   label={option.label}
                                                                   values={values}
                                                                   value={option.value}
                                                                   onClick={this.checkBoxClick}/>
            )}
          </ul>
        </ChoiceMenu>);
    } else if (item.type === 'menu') {
      return (
        <Menu>
          {item.options.map((option, key) => this.choiceOtherAction(option, key))}
        </Menu>
      );
    }
  };

  render() {
    const {id, item, currentParams } = this.props;
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
          } else if (Boolean(currentParams.get(option.param)) === true && currentParams.get(option.param).size > 0) {
            hasValue = true;
          }
        });
        return hasValue;
      }
      return false;
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
                    ignoreNodes={[this.refs.menuItem, '.dpw-navigation-dropdown-panel']}
                    additionalNodes={['.dpw-navigation-dropdown-item-clear']}>
            {this.renderPanel(item)}
          </ClickOut>
        </Detached>
      </li>
    );
  }
}
