import PropTypes from 'prop-types';
// @flow
import React from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { List } from 'immutable';
import { currentAppStateSelector } from '../../../Application/Selectors/dpWindow';
import { selectedSelector } from '../../../Application/Selectors/massActions';
import { toggleMassAction } from '../../../Application/Actions/massActions';

type DefaultProps={};
type Props={selected:List, dispatch: ()=>void, elements: Array<number>};
type State={enabled:boolean};

@connect(state => {
  const currentAppState = currentAppStateSelector(state);

  return {
    elements: currentAppState.list.get('elements'),
    selected: selectedSelector(state)
  };
})
export class MassActionsCheckboxContainer extends React.Component<DefaultProps, Props, State> {
  static defaultProps:{};

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    elements: PropTypes.array,
    selected: PropTypes.object
  };

  constructor(props:Props) {
    super(props);
    this.state = { enabled: Boolean(props.selected.count()) };
  }

  state:State;

  componentWillReceiveProps(nextProps:Props) {
    this.setState({ enabled: Boolean(nextProps.selected.count()) });
  }

  props:Props;

  handleClick:Function = (e:Event):void => {
    e.preventDefault();
    const { dispatch, elements } = this.props;
    dispatch(toggleMassAction({ select: !this.state.enabled, elements }));
  };

  render() {
    const { selected } = this.props;
    const count           = selected.count();
    const divClasses      = classNames('dpwd-navigation-top-row-mass-action-checkbox', { active: this.state.enabled });
    const checkboxClasses = classNames('fa', { 'fa-check': this.state.enabled });

    return (
      <div className="dpwd-navigation-top-row-mass-action-checkbox-container">
        <div className={divClasses} onClick={this.handleClick}>
          <i className={checkboxClasses} />
        </div>
        {count > 0 && <CheckboxCounter count={count} />}
      </div>
    );
  }
}

export const CheckboxCounter = ({ count }:{count: number}) =>
  <div className="dpwd-navigation-top-row-mass-action-checkbox-count">
    <span>{count}</span>
  </div>;

CheckboxCounter.propTypes = { count: PropTypes.number };

