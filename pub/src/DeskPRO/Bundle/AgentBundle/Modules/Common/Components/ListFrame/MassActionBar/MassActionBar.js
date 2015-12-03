import React, { Component, PropTypes } from 'react';
import { ListFrameMenu } from '../ListFrameMenu';
import { ActionContainer } from './ActionContainer';
import classNames from 'classnames';

export class MassActionBar extends Component {
  static propTypes = {
    selected: PropTypes.object.isRequired,
    action: PropTypes.func.isRequired,
    setParams: PropTypes.func.isRequired,
    actions: PropTypes.array.isRequired,
    currentParams: PropTypes.object,
    checkbox: PropTypes.shape({
      count: PropTypes.number.isRequired,
      action: PropTypes.func.isRequired
    })
  };

  render() {
    const { checkbox, actions, action, selected, setParams, currentParams } = this.props;

    return (
      <ListFrameMenu checkbox={checkbox}>
        {actions.map((item, index)=>
            <ActionContainer key={index} id={index}
                             item={item}
                             setParams={setParams} currentParams={currentParams}/>
        )}
        <li>
          <hr/>
        </li>
        <GoMassActionButton action={action}
                            selected={selected}
                            currentParams={currentParams}/>
      </ListFrameMenu>
    );
  }
}

export class GoMassActionButton extends Component {
  static propTypes = {
    selected: PropTypes.object.isRequired,
    currentParams: PropTypes.object,
    action: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    const {currentParams} = props;
    this.state = { isActive: currentParams && currentParams.size > 0 };
  }

  componentWillReceiveProps(nextProps) {
    const {currentParams} = nextProps;
    this.setState({ isActive: currentParams && currentParams.size > 0 });
    return nextProps;
  }

  clickHandler(event) {
    event.preventDefault();
    const {action, selected} = this.props;
    action(selected.toArray());
  }

  render() {
    const classes = classNames('top-row-action-button-link', { 'active': this.state.isActive });

    return (
      <li className="">
        <span
          className="dpwd-navigation-dropdown-top-row-action-button dpwd-navigation-dropdown-top-row-action-button-flat">
          <a href="" className={classes} onClick={this.clickHandler.bind(this)}>
            <span
              className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey">
              Go
            </span>
          </a>
        </span>
      </li>
    );
  }
}