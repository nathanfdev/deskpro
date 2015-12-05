import React, { Component, PropTypes } from 'react';
import classNames from 'classnames';
import Positioned from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import {QuickFilter} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/QuickFilter';
import { RadioChoiceMenuOption } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';

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
    const renderNested = (nested) => {
      if (!nested || !nested.length) {
        return <span />;
      }

      return (
        <ul>
          {nested.map((option, index1) =>
              <RadioChoiceMenuOption
                key={index1}
                isActive={currentParams && currentParams.get(option.param) === option.value}
                value={option.value}
                param={option.param}
                label={option.label}
                resetSingleAction={resetSingleAction}
                setParams={setParams}
                />
          )}
        </ul>
      );
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
          <ClickOut
            onClickOut={this.collapse}
            ignoreNodes={[this.refs.menuItem, '.dpw-navigation-dropdown-panel', '.dpw-label-list']}
            additionalNodes={['.dpw-navigation-dropdown-item-clear']}>
            <div className="dpw-navigation-dropdown-panel" style={{width: '250px'}}>
              <div className="dpw-navigation-dropdown-panel-content">
                <div className="dpw-navigation-dropdown-panel-content-line">
                  <div className="dpw-navigation-dropdown-panel-content-full">
                    <QuickFilter/>
                  </div>
                </div>
                <div className="dpw-navigation-dropdown-panel-content-line">
                  <div className="dpw-navigation-dropdown-panel-content-full">
                    <div className="dpw--popup-item-collection">
                      <ul>
                        {item.options.map((option, index) =>
                            <RadioChoiceMenuOption key={index}
                                                   isActive={currentParams && currentParams.get(item.param) === option.value}
                                                   value={option.value}
                                                   label={option.label}
                                                   param={item.param}
                                                   resetSingleAction={resetSingleAction}
                                                   setParams={setParams}>
                              {renderNested(option.nested)}
                            </RadioChoiceMenuOption>
                        )}
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </ClickOut>
        </Positioned>
      </li>
    );
  }
}

export class Button extends Component {
  static propTypes = {
    isActive: PropTypes.bool,
    hasValue: PropTypes.bool,
    label: PropTypes.string,
    icon: PropTypes.string,
    onClick: PropTypes.func.isRequired
  };

  componentWillMount() {
    this.setState({
      isActive: this.props.isActive
    });
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      isActive: nextProps.isActive
    });
    return nextProps;
  }

  renderContent() {
    const { label, icon } = this.props;
    if (label) {
      return label;
    } else if (icon) {
      const classes = classNames('fa', icon);
      return (<i className={classes}></i>);
    }
  }

  render() {
    const { onClick, hasValue } = this.props;
    const classes = classNames('top-row-action-button-link', { 'active': this.state.isActive, 'has-value': hasValue });

    return (
      <span className="dpwd-navigation-dropdown-top-row-action-button">
          <a href="" className={classes} onClick={onClick}>
            <span
              className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey">
              {this.renderContent()}
            </span>
            <span className="top-row-action-button-link-extra">
              <span className="dpwd-navigation-dropdown-top-row-button-icon">
                <i className="fa fa-caret-down"></i>
              </span>
            </span>
          </a>
        </span>
    );
  }
}