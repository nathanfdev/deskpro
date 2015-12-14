import React, { Component, PropTypes } from 'react';
import Positioned from 'DeskPRO/Component/Positioned/Detached';
import { Button } from './Button';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { DropdownPanel } from './DropdownPanel';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { AddLabelsContainer } from './AddLabelsContainer';
import { RemoveLabelsContainer } from './RemoveLabelsContainer';

export class ActionContainer extends Component {
  static propTypes = {
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
      if (option.type === 'addLabels') {
        return (<AddLabelsContainer key={key} option={option}/>);
      } else if (option.type === 'removeLabels') {
        return (<RemoveLabelsContainer key={key} option={option}/>);
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
        <Positioned isOpen={this.state.expanded}
                    positionAt="left bottom"
                    positionTarget={this.refs['button' + id]}>
          <ClickOut onClickOut={this.collapse}
                    ignoreNodes={[this.refs.menuItem, '.dpw-navigation-dropdown-panel', '.dpw-label-list']}
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
        </Positioned>
      </li>
    );
  }
}
