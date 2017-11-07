import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { Button } from './Button';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { SingleChoicePanelContainer } from '../../../../Common/Components/Form/SingleChoicePanelContainer';
import { setMassActionsParams, resetParam } from '../../../../Application/Actions/massActions';
import { ActionMenuContainer } from './ActionMenuContainer';
import { MultipleActionChoiceContainer } from './MultipleActionChoiceContainer';
import { AssignActionContainer } from './AssignActionContainer';
import { SetDateAction } from './SetDateAction';
import { HtmlReplyActionContainer } from './HtmlReplyActionContainer';

export class MassActionDropdown extends Component {
  static propTypes = {
    isActive:      PropTypes.bool,
    currentParams: PropTypes.object,
    id:            PropTypes.number.isRequired,
    item:          PropTypes.object.isRequired
  };

  componentWillMount() {
    this.setState({ expanded: this.props.isActive });
  }

  toggleExpanded = (event) => {
    event.preventDefault();
    this.setState({ expanded: !this.state.expanded });
  };

  collapse = () => this.setState({ expanded: false });

  renderPanel = (item) => {
    const { currentParams } = this.props;
    if (item.type === 'set_action') {
      return (
        <SingleChoicePanelContainer
          item={item}
          currentParams={currentParams}
          setParams={setMassActionsParams}
          resetSingleAction={resetParam}
        />
      );
    } else if (item.type === 'select_action') {
      return (
        <MultipleActionChoiceContainer
          item={item}
          setParams={setMassActionsParams}
          currentParams={currentParams}
          resetSingleAction={resetParam}
        />
      );
    } else if (item.type === 'assign_action') {
      return (
        <AssignActionContainer
          setParams={setMassActionsParams}
          currentParams={currentParams}
          resetSingleAction={resetParam}
        />
      );
    } else if (item.type === 'menu') {
      return (
        <ActionMenuContainer
          options={item.options}
          setParams={setMassActionsParams}
          currentParams={currentParams}
          resetSingleAction={resetParam}
        />
      );
    } else if (item.type === 'set_date') {
      return (
        <SetDateAction
          param={item.param}
          setParams={setMassActionsParams}
          currentParams={currentParams}
          resetSingleAction={resetParam}
        />
      );
    } else if (item.type === 'mass_reply') {
      return (
        <HtmlReplyActionContainer
          setParams={setMassActionsParams}
          currentParams={currentParams.get('reply')}
          resetSingleAction={resetParam}
        />
      );
    }
    return null;
  };

  render() {
    const { id, item, currentParams } = this.props;

    const checkIfButtonHasValue = () => {
      if (!currentParams) {
        return false;
      }
      if (undefined !== currentParams.get(item.param)) {
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
          } else if (undefined !== currentParams.get(option.param)) {
            hasValue = true;
          }
        });
        return hasValue;
      }
      return false;
    };

    return (
      <li>
        <Button
          isActive={this.state.expanded}
          hasValue={!this.state.expanded && checkIfButtonHasValue()}
          ref={`button${id}`}
          label={item.label}
          icon={item.icon}
          onClick={this.toggleExpanded}
        />
        <Detached
          isOpen={this.state.expanded}
          positionAt="left bottom"
          positionTarget={this.refs[`button${id}`]}
        >
          <ClickOut
            onClickOut={this.collapse}
            ignoreNodes={[this.refs.menuItem, '.dpw-navigation-dropdown-panel']}
            additionalNodes={['.dpw-navigation-dropdown-item-clear']}
          >
            {this.renderPanel(item)}
          </ClickOut>
        </Detached>
      </li>
    );
  }
}
