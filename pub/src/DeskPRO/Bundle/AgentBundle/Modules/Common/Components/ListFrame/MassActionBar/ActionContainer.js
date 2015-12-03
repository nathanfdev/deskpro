import React, { Component, PropTypes } from 'react';
import classNames from 'classnames';
import Positioned from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { RadioChoiceMenuOption } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';

export class ActionContainer extends Component {
  static propTypes = {
    isActive: PropTypes.bool,
    setParams: PropTypes.func.isRequired,
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
    const {id, item, setParams } = this.props;

    return (
      <li>
        <Button isActive={this.state.expanded}
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
            <Menu>
              {item.options.map((option, index) =>
                  <RadioChoiceMenuOption key={index}
                                         value={option.value}
                                         label={option.label}
                                         param={item.param}
                                         onClick={setParams}/>
              )}
            </Menu>
          </ClickOut>
        </Positioned>
      </li>
    );
  }
}

export class Button extends Component {
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
    const classes = classNames('top-row-action-button-link', { 'active': this.state.isActive });
    const { onClick } = this.props;

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